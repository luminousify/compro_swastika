import puppeteer from 'puppeteer';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

(async () => {
  const browser = await puppeteer.launch({
    headless: false, // Set to true for headless mode
    defaultViewport: { width: 1920, height: 1080 },
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });

  const page = await browser.newPage();
  
  // Enable request/response logging
  page.on('console', msg => console.log('PAGE LOG:', msg.text()));
  page.on('pageerror', error => console.log('PAGE ERROR:', error.message));
  page.on('requestfailed', request => console.log('FAILED REQUEST:', request.url(), request.failure().errorText));
  
  const screenshots = [];
  let stepCounter = 1;

  const takeScreenshot = async (description) => {
    const filename = `screenshot-${stepCounter}-${description.replace(/[^a-zA-Z0-9]/g, '-')}.png`;
    const filepath = path.join(__dirname, filename);
    await page.screenshot({ path: filepath, fullPage: true });
    screenshots.push({ step: stepCounter, description, filename, filepath });
    console.log(`📸 Screenshot ${stepCounter}: ${description} -> ${filename}`);
    stepCounter++;
  };

  try {
    console.log('🚀 Starting production site test...');

    // Step 1: Navigate to login page
    console.log('Step 1: Navigating to login page...');
    await page.goto('https://dayaswastika.com/login', { waitUntil: 'networkidle2' });
    await takeScreenshot('login-page-loaded');

    // Step 2: Login with credentials
    console.log('Step 2: Attempting to login...');
    
    // Wait for login form elements
    await page.waitForSelector('input[name="email"]', { timeout: 10000 });
    await page.waitForSelector('input[name="password"]', { timeout: 10000 });
    
    // Fill in credentials
    await page.type('input[name="email"]', 'admin@dsp.co.id');
    await page.type('input[name="password"]', 'password');
    
    await takeScreenshot('credentials-filled');
    
    // Submit login form
    await page.click('button[type="submit"]');
    await page.waitForNavigation({ waitUntil: 'networkidle2' });
    
    await takeScreenshot('after-login');
    
    // Check if login was successful by looking for admin elements
    const currentUrl = page.url();
    console.log('Current URL after login:', currentUrl);
    
    if (currentUrl.includes('/admin')) {
      console.log('✅ Login successful - redirected to admin area');
    } else {
      console.log('❌ Login may have failed - not in admin area');
    }

    // Step 3: Navigate to divisions/adhesive page
    console.log('Step 3: Navigating to divisions/adhesive page...');
    await page.goto('https://dayaswastika.com/admin/divisions/adhesive', { waitUntil: 'networkidle2' });
    await takeScreenshot('divisions-adhesive-page');

    // Step 4: Look for Add Product button
    console.log('Step 4: Looking for Add Product button...');
    
    // Try different possible selectors for the Add Product button
    const possibleSelectors = [
      'a[href*="products/create"]',
      'button:contains("Add Product")',
      'a:contains("Add Product")',
      '.btn:contains("Add Product")',
      '[data-action="add-product"]',
      'a[href*="/admin/products/create"]'
    ];

    let addProductButton = null;
    let buttonSelector = null;

    for (const selector of possibleSelectors) {
      try {
        if (selector.includes(':contains')) {
          // Use evaluate for text-based selection
          const button = await page.evaluateHandle(() => {
            const elements = Array.from(document.querySelectorAll('a, button'));
            return elements.find(el => el.textContent.includes('Add Product') || el.textContent.includes('Tambah Produk'));
          });
          if (button && await button.evaluate(el => el !== null)) {
            addProductButton = button;
            buttonSelector = 'text-based selector';
            break;
          }
        } else {
          addProductButton = await page.$(selector);
          if (addProductButton) {
            buttonSelector = selector;
            break;
          }
        }
      } catch (e) {
        // Continue trying other selectors
      }
    }

    if (addProductButton) {
      console.log(`✅ Add Product button found using: ${buttonSelector}`);
      
      // Get button properties
      const buttonProperties = await page.evaluate(() => {
        const elements = Array.from(document.querySelectorAll('a, button'));
        const button = elements.find(el => el.textContent.includes('Add Product') || el.textContent.includes('Tambah Produk'));
        if (button) {
          return {
            tagName: button.tagName,
            className: button.className,
            href: button.href,
            textContent: button.textContent.trim(),
            disabled: button.disabled,
            style: window.getComputedStyle(button).display
          };
        }
        return null;
      });
      
      console.log('Button properties:', buttonProperties);
      
      // Highlight the button in the screenshot
      await page.evaluate(() => {
        const elements = Array.from(document.querySelectorAll('a, button'));
        const button = elements.find(el => el.textContent.includes('Add Product') || el.textContent.includes('Tambah Produk'));
        if (button) {
          button.style.border = '3px solid red';
          button.style.backgroundColor = 'yellow';
        }
      });
      
      await takeScreenshot('add-product-button-highlighted');

      // Step 5: Try clicking the Add Product button
      console.log('Step 5: Clicking Add Product button...');
      
      const urlBeforeClick = page.url();
      console.log('URL before click:', urlBeforeClick);
      
      // Listen for navigation
      const navigationPromise = page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 5000 }).catch(() => null);
      
      // Click the button
      await page.evaluate(() => {
        const elements = Array.from(document.querySelectorAll('a, button'));
        const button = elements.find(el => el.textContent.includes('Add Product') || el.textContent.includes('Tambah Produk'));
        if (button) {
          button.click();
        }
      });
      
      // Wait a moment to see if navigation occurs
      await Promise.race([navigationPromise, new Promise(resolve => setTimeout(resolve, 3000))]);
      
      const urlAfterClick = page.url();
      console.log('URL after click:', urlAfterClick);
      
      await takeScreenshot('after-button-click');
      
      if (urlAfterClick !== urlBeforeClick) {
        console.log('✅ Button click caused navigation');
        
        // Step 6: Test create product page if we navigated there
        if (urlAfterClick.includes('create')) {
          console.log('Step 6: Testing create product page...');
          await takeScreenshot('create-product-page');
          
          // Check for form elements
          const formElements = await page.evaluate(() => {
            const forms = document.querySelectorAll('form');
            const inputs = document.querySelectorAll('input, textarea, select');
            return {
              formsCount: forms.length,
              inputsCount: inputs.length,
              hasNameField: !!document.querySelector('input[name="name"], input[name="title"]'),
              hasDescriptionField: !!document.querySelector('textarea[name="description"]'),
              hasSubmitButton: !!document.querySelector('button[type="submit"], input[type="submit"]')
            };
          });
          
          console.log('Create page form elements:', formElements);
        }
      } else {
        console.log('❌ Button click did not cause navigation');
      }
      
    } else {
      console.log('❌ Add Product button not found');
      
      // Let's see what buttons/links are available
      const availableButtons = await page.evaluate(() => {
        const buttons = Array.from(document.querySelectorAll('a, button'));
        return buttons.map(btn => ({
          tagName: btn.tagName,
          textContent: btn.textContent.trim(),
          href: btn.href,
          className: btn.className
        })).filter(btn => btn.textContent.length > 0);
      });
      
      console.log('Available buttons/links on page:', availableButtons);
    }

    // Step 7: Check for JavaScript errors
    console.log('Step 7: Checking for JavaScript errors...');
    // Errors are already logged via page.on('pageerror') above

    // Step 8: Check network tab for failed requests
    console.log('Step 8: Monitoring network requests...');
    // Failed requests are already logged via page.on('requestfailed') above

    await takeScreenshot('final-state');

  } catch (error) {
    console.error('❌ Test failed with error:', error.message);
    await takeScreenshot('error-state');
  } finally {
    console.log('\n📋 Test Summary:');
    console.log('Screenshots taken:', screenshots.length);
    screenshots.forEach(shot => {
      console.log(`  ${shot.step}. ${shot.description} -> ${shot.filename}`);
    });
    
    await browser.close();
  }
})();