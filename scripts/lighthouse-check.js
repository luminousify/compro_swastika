#!/usr/bin/env node

/**
 * Lighthouse Performance Check Script
 * 
 * This script runs Lighthouse audits and checks if performance metrics
 * meet the required thresholds (LCP < 2.5s, CLS < 0.1)
 */

const lighthouse = require('lighthouse');
const chromeLauncher = require('chrome-launcher');
const fs = require('fs');
const path = require('path');

// Performance thresholds
const THRESHOLDS = {
    LCP: 2500, // 2.5 seconds in milliseconds
    CLS: 0.1,  // Cumulative Layout Shift
    FCP: 1800, // First Contentful Paint
    SI: 3400,  // Speed Index
    TBT: 200,  // Total Blocking Time
    PERFORMANCE_SCORE: 90 // Overall performance score
};

// URLs to test
const URLS_TO_TEST = [
    '/',
    '/visi-misi',
    '/milestones',
    '/line-of-business',
    '/contact'
];

class LighthouseChecker {
    constructor(baseUrl = 'http://localhost:8000') {
        this.baseUrl = baseUrl;
        this.results = [];
        this.chrome = null;
    }

    async init() {
        this.chrome = await chromeLauncher.launch({
            chromeFlags: ['--headless', '--disable-gpu', '--no-sandbox']
        });
    }

    async runAudit(url) {
        const fullUrl = this.baseUrl + url;
        console.log(`Running Lighthouse audit for: ${fullUrl}`);

        const options = {
            logLevel: 'info',
            output: 'json',
            onlyCategories: ['performance'],
            port: this.chrome.port,
            throttling: {
                rttMs: 40,
                throughputKbps: 10240,
                cpuSlowdownMultiplier: 1,
                requestLatencyMs: 0,
                downloadThroughputKbps: 0,
                uploadThroughputKbps: 0
            }
        };

        try {
            const runnerResult = await lighthouse(fullUrl, options);
            const { lhr } = runnerResult;

            const metrics = {
                url: url,
                performanceScore: Math.round(lhr.categories.performance.score * 100),
                lcp: lhr.audits['largest-contentful-paint'].numericValue,
                cls: lhr.audits['cumulative-layout-shift'].numericValue,
                fcp: lhr.audits['first-contentful-paint'].numericValue,
                si: lhr.audits['speed-index'].numericValue,
                tbt: lhr.audits['total-blocking-time'].numericValue
            };

            this.results.push(metrics);
            return metrics;
        } catch (error) {
            console.error(`Error auditing ${fullUrl}:`, error.message);
            return null;
        }
    }

    async runAllAudits() {
        console.log('Starting Lighthouse performance audits...\n');

        for (const url of URLS_TO_TEST) {
            await this.runAudit(url);
            // Add delay between audits to avoid overwhelming the server
            await this.delay(2000);
        }
    }

    checkThresholds() {
        console.log('\n=== Performance Results ===\n');

        let allPassed = true;
        const failures = [];

        this.results.forEach(result => {
            if (!result) return;

            console.log(`URL: ${result.url}`);
            console.log(`Performance Score: ${result.performanceScore}/100`);
            console.log(`LCP: ${Math.round(result.lcp)}ms (threshold: ${THRESHOLDS.LCP}ms)`);
            console.log(`CLS: ${result.cls.toFixed(3)} (threshold: ${THRESHOLDS.CLS})`);
            console.log(`FCP: ${Math.round(result.fcp)}ms (threshold: ${THRESHOLDS.FCP}ms)`);
            console.log(`Speed Index: ${Math.round(result.si)}ms (threshold: ${THRESHOLDS.SI}ms)`);
            console.log(`Total Blocking Time: ${Math.round(result.tbt)}ms (threshold: ${THRESHOLDS.TBT}ms)`);

            // Check thresholds
            const checks = [
                { name: 'Performance Score', value: result.performanceScore, threshold: THRESHOLDS.PERFORMANCE_SCORE, operator: '>=' },
                { name: 'LCP', value: result.lcp, threshold: THRESHOLDS.LCP, operator: '<=' },
                { name: 'CLS', value: result.cls, threshold: THRESHOLDS.CLS, operator: '<=' },
                { name: 'FCP', value: result.fcp, threshold: THRESHOLDS.FCP, operator: '<=' },
                { name: 'Speed Index', value: result.si, threshold: THRESHOLDS.SI, operator: '<=' },
                { name: 'Total Blocking Time', value: result.tbt, threshold: THRESHOLDS.TBT, operator: '<=' }
            ];

            checks.forEach(check => {
                const passed = check.operator === '>=' 
                    ? check.value >= check.threshold 
                    : check.value <= check.threshold;

                if (!passed) {
                    allPassed = false;
                    failures.push({
                        url: result.url,
                        metric: check.name,
                        value: check.value,
                        threshold: check.threshold,
                        operator: check.operator
                    });
                    console.log(`❌ ${check.name} FAILED`);
                } else {
                    console.log(`✅ ${check.name} PASSED`);
                }
            });

            console.log('---\n');
        });

        return { allPassed, failures };
    }

    generateReport() {
        const timestamp = new Date().toISOString();
        const report = {
            timestamp,
            baseUrl: this.baseUrl,
            thresholds: THRESHOLDS,
            results: this.results
        };

        const reportPath = path.join(__dirname, '..', 'storage', 'lighthouse-report.json');
        
        // Ensure directory exists
        const dir = path.dirname(reportPath);
        if (!fs.existsSync(dir)) {
            fs.mkdirSync(dir, { recursive: true });
        }

        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
        console.log(`Report saved to: ${reportPath}`);

        return reportPath;
    }

    async cleanup() {
        if (this.chrome) {
            await this.chrome.kill();
        }
    }

    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}

// Main execution
async function main() {
    const baseUrl = process.argv[2] || 'http://localhost:8000';
    const checker = new LighthouseChecker(baseUrl);

    try {
        await checker.init();
        await checker.runAllAudits();
        
        const { allPassed, failures } = checker.checkThresholds();
        checker.generateReport();

        if (!allPassed) {
            console.log('\n❌ PERFORMANCE CHECK FAILED\n');
            console.log('Failures:');
            failures.forEach(failure => {
                console.log(`- ${failure.url}: ${failure.metric} = ${failure.value} (should be ${failure.operator} ${failure.threshold})`);
            });
            process.exit(1);
        } else {
            console.log('\n✅ ALL PERFORMANCE CHECKS PASSED\n');
            process.exit(0);
        }
    } catch (error) {
        console.error('Error running Lighthouse checks:', error);
        process.exit(1);
    } finally {
        await checker.cleanup();
    }
}

// Run if called directly
if (require.main === module) {
    main();
}

module.exports = { LighthouseChecker, THRESHOLDS };