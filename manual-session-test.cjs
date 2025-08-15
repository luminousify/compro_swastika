const puppeteer = require('puppeteer');

async function manualSessionTest() {
    console.log('=== Manual Session Expiration Test ===\n');
    console.log('This test will open a browser for manual testing.');
    console.log('Please follow the instructions step by step.\n');
    
    const browser = await puppeteer.launch({ 
        headless: false, 
        devtools: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    const page = await browser.newPage();
    
    try {
        console.log('Step 1: Opening login page...');
        await page.goto('http://localhost:8000/login', { waitUntil: 'networkidle2' });
        
        console.log('\n=== MANUAL TESTING INSTRUCTIONS ===');
        console.log('Browser is now open. Please manually:');
        console.log('');
        console.log('1. LOGIN NORMALLY:');
        console.log('   - Email: admin@dsp.com');
        console.log('   - Password: admin123');
        console.log('   - Click Sign In');
        console.log('   - Verify you reach the admin dashboard or change password page');
        console.log('');
        console.log('2. SIMULATE SESSION EXPIRATION:');
        console.log('   - Open DevTools (F12)');
        console.log('   - Go to Application/Storage tab');
        console.log('   - Find "dsp_company_profile_session" cookie');
        console.log('   - Note its value for reference');
        console.log('   - Delete the session cookie OR modify its value to make it invalid');
        console.log('');
        console.log('3. TEST THE ISSUE:');
        console.log('   - Try to navigate to http://localhost:8000/login');
        console.log('   - Observe what happens:');
        console.log('     a) Can you access the login page?');
        console.log('     b) Are you redirected elsewhere?');
        console.log('     c) Do you get any error messages?');
        console.log('     d) Is the login form present and functional?');
        console.log('');
        console.log('4. TEST PROTECTED ROUTES:');
        console.log('   - Try to navigate to http://localhost:8000/admin/dashboard');
        console.log('   - You should be redirected to login (this should work correctly)');
        console.log('');
        console.log('5. DOCUMENT OBSERVATIONS:');
        console.log('   - Note any redirects, error messages, or unexpected behavior');
        console.log('   - Test if you can successfully login after session expiration');
        console.log('');
        console.log('=== EXPECTED vs ACTUAL BEHAVIOR ===');
        console.log('Expected: Expired session users can access /login and re-authenticate');
        console.log('Potential Issue: Guest middleware may incorrectly consider expired sessions as "authenticated"');
        console.log('');
        console.log('The browser will remain open for 15 minutes for testing.');
        console.log('Close the browser window when you are done testing.');
        
        // Keep browser open for manual testing
        await new Promise(resolve => setTimeout(resolve, 900000)); // 15 minutes
        
    } catch (error) {
        console.error('Error:', error);
    } finally {
        console.log('\nTest session ended. Closing browser...');
        await browser.close();
    }
}

console.log('Starting manual session expiration test...');
console.log('Make sure Laravel server is running on http://localhost:8000');
console.log('');

manualSessionTest();