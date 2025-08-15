const puppeteer = require('puppeteer');

(async () => {
  console.log('🚀 Starting Puppeteer test for product creation functionality...');
  
  const browser = await puppeteer.launch({ 
    headless: false,
    slowMo: 1000,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });
  
  const page = await browser.newPage();
  
  // Set viewport
  await page.setViewport({ width: 1280, height: 720 });
  
  // Monitor console errors
  const consoleErrors = [];
  page.on('console', msg => {
    if (msg.type() === 'error') {
      consoleErrors.push(msg.text());
      console.log('❌ Console Error:', msg.text());
    }
  });
  
  try {
    console.log('🔗 Step 1: Navigating to admin login page...');
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle0' });
    
    // Take screenshot of login page
    await page.screenshot({ path: 'login-page.png' });
    console.log('📸 Screenshot saved: login-page.png');
    
    // Check if login form exists
    const loginForm = await page.$('form');
    const loginFormExists = loginForm !== null;
    console.log('✅ Login form exists:', loginFormExists);
    
    if (!loginFormExists) {
      throw new Error('Login form not found on the page');
    }
    
    console.log('✅ Step 1 completed: Successfully navigated to admin login page');
    
    // Step 2: Login with admin credentials
    console.log('🔑 Step 2: Logging in with admin credentials...');
    
    await page.type('input[name="email"]', 'admin@dsp.com');
    await page.type('input[name="password"]', 'admin123');
    
    // Click login button
    await page.click('button[type="submit"]');
    await page.waitForNavigation({ waitUntil: 'networkidle0' });
    
    // Check if we're on dashboard
    const currentUrl = page.url();
    console.log('📍 Current URL after login:', currentUrl);
    
    if (!currentUrl.includes('/admin/dashboard')) {
      throw new Error('Login failed - not redirected to dashboard');
    }
    
    console.log('✅ Step 2 completed: Successfully logged in as admin');
    
    // Step 3: Navigate to divisions
    console.log('🏢 Step 3: Navigating to divisions...');
    
    await page.goto('http://127.0.0.1:8000/admin/divisions', { waitUntil: 'networkidle0' });
    await page.screenshot({ path: 'divisions-page.png' });
    
    // Find first division link
    const divisionLink = await page.$('a[href*="/admin/divisions/"]');
    if (!divisionLink) {
      throw new Error('No division links found');
    }
    
    // Click on the first division
    await divisionLink.click();
    await page.waitForNavigation({ waitUntil: 'networkidle0' });
    
    console.log('✅ Step 3 completed: Successfully navigated to division detail page');
    
    // Step 4: Access product creation form
    console.log('📦 Step 4: Accessing product creation form...');
    
    // Look for "Create Product" button or link
    const createProductButton = await page.$('a[href*="/products/create"], button:contains("Create Product"), a:contains("Create Product")');
    
    if (!createProductButton) {
      // Try to navigate directly to create product page
      const currentUrl = page.url();
      const divisionId = currentUrl.split('/').pop();
      await page.goto(`http://127.0.0.1:8000/admin/divisions/${divisionId}/products/create`, { waitUntil: 'networkidle0' });
    } else {
      await createProductButton.click();
      await page.waitForNavigation({ waitUntil: 'networkidle0' });
    }
    
    await page.screenshot({ path: 'product-create-form.png' });
    
    // Check if product form exists
    const productForm = await page.$('form');
    if (!productForm) {
      throw new Error('Product creation form not found');
    }
    
    console.log('✅ Step 4 completed: Successfully accessed product creation form');
    
    // Step 5: Fill out product form
    console.log('📝 Step 5: Filling out product form...');
    
    await page.type('input[name="name"]', 'Test Product');
    await page.type('textarea[name="description"]', 'This is a test product description');
    
    console.log('✅ Step 5 completed: Successfully filled out product form');
    
    // Step 6: Submit form and check for errors
    console.log('🚀 Step 6: Submitting product form...');
    
    // Submit the form
    await page.click('button[type="submit"]');
    
    // Wait for either navigation or error
    try {
      await page.waitForNavigation({ waitUntil: 'networkidle0', timeout: 10000 });
    } catch (timeoutError) {
      // Check if we got a 419 error or page expired
      const pageContent = await page.content();
      if (pageContent.includes('419') || pageContent.includes('Page Expired')) {
        throw new Error('❌ CSRF Token Error: Page Expired (419) - Form enhancement fix did not work');
      }
    }
    
    const finalUrl = page.url();
    console.log('📍 Final URL after form submission:', finalUrl);
    
    await page.screenshot({ path: 'after-form-submission.png' });
    
    // Check for success indicators
    const pageContent = await page.content();
    const hasSuccessMessage = pageContent.includes('successfully') || pageContent.includes('created') || finalUrl.includes('/admin/divisions/');
    
    if (pageContent.includes('419') || pageContent.includes('Page Expired')) {
      throw new Error('❌ CSRF Token Error: Page Expired (419) detected');
    }
    
    console.log('✅ Step 6 completed: Form submitted without CSRF errors');
    
    // Step 7: Verify creation success
    console.log('🎯 Step 7: Verifying product creation success...');
    
    if (hasSuccessMessage) {
      console.log('✅ Product creation appears successful');
    } else {
      console.log('⚠️  Product creation status unclear - check screenshots');
    }
    
    // Report console errors
    if (consoleErrors.length > 0) {
      console.log('❌ JavaScript Console Errors Detected:');
      consoleErrors.forEach(error => console.log('  -', error));
    } else {
      console.log('✅ No JavaScript console errors detected');
    }
    
    console.log('✅ Step 7 completed: Verification complete');
    
  } catch (error) {
    console.error('❌ Test Error:', error.message);
    await page.screenshot({ path: 'error-final.png' });
  } finally {
    await browser.close();
    console.log('🏁 Test completed');
  }
})();