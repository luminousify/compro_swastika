const puppeteer = require('puppeteer');

(async () => {
  console.log('🚀 Starting simplified Puppeteer test for product creation...');
  
  const browser = await puppeteer.launch({ 
    headless: true,  // Run in headless mode for speed
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });
  
  const page = await browser.newPage();
  
  // Monitor console errors
  const consoleErrors = [];
  page.on('console', msg => {
    if (msg.type() === 'error') {
      consoleErrors.push(msg.text());
      console.log('❌ Console Error:', msg.text());
    }
  });
  
  try {
    console.log('🔗 Step 1: Testing login page access...');
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle0', timeout: 30000 });
    
    // Check if login form exists
    const loginForm = await page.$('form');
    const loginFormExists = loginForm !== null;
    console.log('✅ Login form exists:', loginFormExists);
    
    if (!loginFormExists) {
      throw new Error('Login form not found on the page');
    }
    
    console.log('🔑 Step 2: Attempting login...');
    
    // Fill login form
    await page.type('input[name="email"]', 'admin@dsp.com');
    await page.type('input[name="password"]', 'admin123');
    
    // Submit form and wait for navigation
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle0', timeout: 15000 }),
      page.click('button[type="submit"]')
    ]);
    
    const currentUrl = page.url();
    console.log('📍 Current URL after login:', currentUrl);
    
    // Check if we're authenticated (should be redirected to admin dashboard)
    if (currentUrl.includes('/admin/dashboard') || currentUrl.includes('/admin')) {
      console.log('✅ Login successful');
    } else {
      console.log('⚠️  Login may have failed - unexpected URL');
    }
    
    console.log('🏢 Step 3: Navigating to divisions...');
    await page.goto('http://127.0.0.1:8000/admin/divisions', { waitUntil: 'networkidle0', timeout: 15000 });
    
    // Check for divisions content
    const divisionsContent = await page.content();
    if (divisionsContent.includes('divisions') || divisionsContent.includes('Division')) {
      console.log('✅ Divisions page loaded');
    } else {
      console.log('⚠️  Divisions page may not have loaded correctly');
    }
    
    // Find division show/view links (not create links)
    const divisionLinks = await page.$$('a[href*="/admin/divisions/"]:not([href*="/create"])');
    if (divisionLinks.length > 0) {
      console.log(`✅ Found ${divisionLinks.length} division links`);
      
      // Get the href of the first division link to see what we're clicking
      const firstLinkHref = await page.evaluate(el => el.href, divisionLinks[0]);
      console.log('🔗 Clicking division link:', firstLinkHref);
      
      // Click on the first division
      await divisionLinks[0].click();
      await page.waitForNavigation({ waitUntil: 'networkidle0', timeout: 15000 });
      
      const divisionUrl = page.url();
      console.log('📍 Division detail URL:', divisionUrl);
      
      console.log('📦 Step 4: Testing product creation form access...');
      
      // Try to navigate to product creation form
      const divisionId = divisionUrl.split('/').pop();
      const productCreateUrl = `http://127.0.0.1:8000/admin/divisions/${divisionId}/products/create`;
      
      await page.goto(productCreateUrl, { waitUntil: 'networkidle0', timeout: 15000 });
      
      // Check if product form exists
      const productForm = await page.$('form');
      if (productForm) {
        console.log('✅ Product creation form found');
        
        console.log('📝 Step 5: Testing form fill and submit...');
        
        // Fill out the form
        const nameInput = await page.$('input[name="name"]');
        const descriptionInput = await page.$('textarea[name="description"]');
        
        if (nameInput && descriptionInput) {
          await page.type('input[name="name"]', 'Test Product');
          await page.type('textarea[name="description"]', 'This is a test product description');
          
          console.log('📋 Form filled with test data');
          
          // Check for CSRF token
          const csrfToken = await page.$('input[name="_token"]');
          if (csrfToken) {
            console.log('✅ CSRF token found in form');
          } else {
            console.log('⚠️  No CSRF token found in form');
          }
          
          // Check for form enhancement skip attribute
          const formElement = await page.$('form[data-skip-form-enhancement="true"]');
          if (formElement) {
            console.log('✅ Form has data-skip-form-enhancement="true" attribute');
          } else {
            console.log('⚠️  Form does not have data-skip-form-enhancement attribute');
          }
          
          console.log('🚀 Step 6: Submitting form...');
          
          // Try form submission via JavaScript to avoid click issues  
          const submissionResult = await page.evaluate(() => {
            // Find the product creation form specifically (not logout form)
            const forms = document.querySelectorAll('form');
            let productForm = null;
            
            for (let form of forms) {
              // Look for form that has product-related fields or action
              if (form.querySelector('input[name="name"]') || 
                  form.querySelector('textarea[name="description"]') ||
                  form.action.includes('products')) {
                productForm = form;
                break;
              }
            }
            
            if (productForm) {
              // Get form action and method
              const action = productForm.action;
              const method = productForm.method;
              
              // Get all form data
              const formData = new FormData(productForm);
              const data = {};
              for (let [key, value] of formData.entries()) {
                data[key] = value;
              }
              
              // Submit the form
              productForm.submit();
              
              return { action, method, data, submitted: true };
            }
            return { submitted: false };
          });
          
          console.log('📋 Form submission attempt:', submissionResult);
          
          // Wait for navigation or timeout
          try {
            await page.waitForNavigation({ waitUntil: 'networkidle0', timeout: 10000 });
          } catch (timeoutError) {
            console.log('⏱️  Navigation timeout - checking page content');
          }
          
          // Wait a bit more to see what happens
          await new Promise(resolve => setTimeout(resolve, 2000));
          
          const finalUrl = page.url();
          const pageContent = await page.content();
          
          console.log('📍 Final URL after submission:', finalUrl);
          
          // Check for CSRF errors
          if (pageContent.includes('419') || pageContent.includes('Page Expired')) {
            console.log('❌ CSRF Token Error: Page Expired (419) detected');
          } else {
            console.log('✅ No CSRF errors detected');
          }
          
          // Check for success indicators
          if (pageContent.includes('successfully') || pageContent.includes('created') || finalUrl !== productCreateUrl) {
            console.log('✅ Form submission appears successful');
          } else {
            console.log('⚠️  Form submission result unclear');
          }
          
        } else {
          console.log('❌ Required form fields not found');
        }
      } else {
        console.log('❌ Product creation form not found');
      }
    } else {
      console.log('❌ No division links found');
    }
    
    // Report console errors
    if (consoleErrors.length > 0) {
      console.log('❌ JavaScript Console Errors Detected:');
      consoleErrors.forEach(error => console.log('  -', error));
    } else {
      console.log('✅ No JavaScript console errors detected');
    }
    
  } catch (error) {
    console.error('❌ Test Error:', error.message);
  } finally {
    await browser.close();
    console.log('🏁 Test completed');
  }
})();