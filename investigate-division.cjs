const puppeteer = require('puppeteer');

(async () => {
  console.log('🚀 Starting Puppeteer investigation...');
  
  const browser = await puppeteer.launch({
    headless: false,
    devtools: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });
  
  const page = await browser.newPage();
  
  // Set viewport
  await page.setViewport({ width: 1920, height: 1080 });
  
  // Enable console logging
  page.on('console', msg => {
    console.log('BROWSER CONSOLE [' + msg.type() + ']:', msg.text());
  });
  
  // Enable request monitoring
  const failedRequests = [];
  page.on('requestfailed', request => {
    const failure = {
      method: request.method(),
      url: request.url(),
      error: request.failure().errorText
    };
    failedRequests.push(failure);
    console.log('FAILED REQUEST:', failure);
  });
  
  const errorResponses = [];
  page.on('response', response => {
    if (!response.ok()) {
      const error = {
        status: response.status(),
        url: response.url()
      };
      errorResponses.push(error);
      console.log('ERROR RESPONSE:', error);
    }
  });
  
  try {
    console.log('📍 Navigating to login page...');
    await page.goto('https://dayaswastika.com/login', {
      waitUntil: 'networkidle2',
      timeout: 30000
    });
    
    console.log('🔐 Attempting to login...');
    
    // Wait for login form to load
    await page.waitForSelector('input[name="email"]', { timeout: 10000 });
    
    // Fill login form
    await page.type('input[name="email"]', 'admin@dsp.co.id');
    
    // Note: We need the actual password to proceed
    // For now, let's check if we can still access the page
    console.log('ℹ️ Need actual password to complete login. Proceeding to check public access...');
    
    console.log('📍 Navigating directly to division page...');
    await page.goto('https://dayaswastika.com/admin/divisions/adhesive', {
      waitUntil: 'networkidle2',
      timeout: 30000
    });
    
    console.log('✅ Division page loaded, current URL:', page.url());
    
    // Take initial screenshot
    await page.screenshot({ 
      path: 'division-page-initial.png', 
      fullPage: true 
    });
    console.log('📸 Initial screenshot saved');
    
    // Check if redirected to login (authentication required)
    if (page.url().includes('/login')) {
      console.log('❌ Redirected to login - authentication required');
      await browser.close();
      return;
    }
    
    // Check if Add Product button exists
    console.log('🔍 Looking for Add Product button...');
    
    // Look for buttons containing "product" text
    const productButtons = await page.evaluate(() => {
      const elements = Array.from(document.querySelectorAll('a, button'));
      return elements
        .filter(el => el.textContent.toLowerCase().includes('product'))
        .map(el => {
          const rect = el.getBoundingClientRect();
          return {
            tagName: el.tagName,
            text: el.textContent.trim(),
            href: el.href || 'N/A',
            className: el.className,
            id: el.id,
            visible: rect.width > 0 && rect.height > 0,
            rect: {
              x: rect.x,
              y: rect.y,
              width: rect.width,
              height: rect.height
            }
          };
        });
    });
    
    console.log('Found product-related buttons:', productButtons);
    
    // Look for Add buttons
    const addButtons = await page.evaluate(() => {
      const elements = Array.from(document.querySelectorAll('a, button'));
      return elements
        .filter(el => el.textContent.toLowerCase().includes('add') || 
                     el.textContent.toLowerCase().includes('tambah') ||
                     el.href && el.href.includes('create'))
        .map(el => {
          const rect = el.getBoundingClientRect();
          return {
            tagName: el.tagName,
            text: el.textContent.trim(),
            href: el.href || 'N/A',
            className: el.className,
            id: el.id,
            visible: rect.width > 0 && rect.height > 0,
            rect: {
              x: rect.x,
              y: rect.y,
              width: rect.width,
              height: rect.height
            }
          };
        });
    });
    
    console.log('Found add/create buttons:', addButtons);
    
    // Look for specific href patterns
    const createLinks = await page.evaluate(() => {
      const links = Array.from(document.querySelectorAll('a[href]'));
      return links
        .filter(el => el.href.includes('create') || el.href.includes('products'))
        .map(el => {
          const rect = el.getBoundingClientRect();
          return {
            tagName: el.tagName,
            text: el.textContent.trim(),
            href: el.href,
            className: el.className,
            id: el.id,
            visible: rect.width > 0 && rect.height > 0
          };
        });
    });
    
    console.log('Found create/product links:', createLinks);
    
    // Get all buttons and links for comprehensive view
    const allInteractiveElements = await page.evaluate(() => {
      const elements = Array.from(document.querySelectorAll('button, a, [role="button"]'));
      return elements.map(el => ({
        tagName: el.tagName,
        text: el.textContent.trim().substring(0, 50), // Limit text length
        href: el.href || 'N/A',
        className: el.className,
        id: el.id
      })).filter(el => el.text.length > 0);
    });
    
    console.log('All interactive elements (first 20):');
    allInteractiveElements.slice(0, 20).forEach((el, i) => {
      console.log(i + 1 + '.', el);
    });
    
    // Check page structure
    const pageStructure = await page.evaluate(() => {
      return {
        title: document.title,
        h1: Array.from(document.querySelectorAll('h1')).map(h => h.textContent.trim()),
        h2: Array.from(document.querySelectorAll('h2')).map(h => h.textContent.trim()),
        hasTable: document.querySelector('table') !== null,
        hasForm: document.querySelector('form') !== null,
        bodyClasses: document.body.className,
        mainContent: document.querySelector('main') ? document.querySelector('main').textContent.substring(0, 200) : 'No main element'
      };
    });
    
    console.log('Page structure:', pageStructure);
    
    // Summary
    console.log('\n📊 INVESTIGATION SUMMARY:');
    console.log('Page title:', pageStructure.title);
    console.log('Product-related buttons found:', productButtons.length);
    console.log('Add/Create buttons found:', addButtons.length);
    console.log('Create/Product links found:', createLinks.length);
    console.log('Failed requests:', failedRequests.length);
    console.log('Error responses:', errorResponses.length);
    
    if (failedRequests.length > 0) {
      console.log('Failed requests details:');
      failedRequests.forEach((req, i) => console.log('  ' + (i+1) + '.', req));
    }
    
    if (errorResponses.length > 0) {
      console.log('Error responses details:');
      errorResponses.forEach((res, i) => console.log('  ' + (i+1) + '.', res));
    }
    
  } catch (error) {
    console.error('❌ Error during investigation:', error.message);
    
    try {
      // Take error screenshot
      await page.screenshot({ 
        path: 'division-page-error.png', 
        fullPage: true 
      });
      console.log('📸 Error screenshot saved');
    } catch (screenshotError) {
      console.error('Failed to take error screenshot:', screenshotError.message);
    }
  }
  
  await browser.close();
  console.log('🏁 Investigation complete');
})();