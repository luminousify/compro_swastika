const puppeteer = require('puppeteer');

(async () => {
  const browser = await puppeteer.launch({
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-web-security']
  });

  console.log('Testing Production Login Page...');
  const productionPage = await browser.newPage();
  
  // Set a realistic user agent to potentially bypass bot detection
  await productionPage.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
  
  // Enable console logging
  productionPage.on('console', msg => console.log('PROD CONSOLE:', msg.text()));
  productionPage.on('pageerror', error => console.log('PROD PAGE ERROR:', error.message));
  
  // Track network requests
  const responses = [];
  productionPage.on('response', response => {
    if (response.url().includes('.css') || response.url().includes('.js') || response.url().includes('build')) {
      responses.push({ 
        url: response.url(), 
        status: response.status(),
        contentType: response.headers()['content-type'] || 'unknown'
      });
    }
  });
  
  try {
    console.log('Attempting to access production login page...');
    await productionPage.goto('https://dayaswastika.com/login', { 
      waitUntil: 'networkidle2',
      timeout: 30000 
    });
    
    await productionPage.screenshot({ 
      path: 'production-login-final-screenshot.png',
      fullPage: true 
    });
    
    console.log('\nNetwork requests for CSS/JS/Build files:');
    responses.forEach(r => console.log('-', r.url, '(Status:', r.status, ', Type:', r.contentType, ')'));
    
    // Check if the page contains expected elements
    const titleText = await productionPage.title();
    console.log('\nPage title:', titleText);
    
    // Check page content
    const pageContent = await productionPage.evaluate(() => {
      const isLoginPage = document.querySelector('form') !== null && 
                         document.querySelector('input[type="email"]') !== null &&
                         document.querySelector('input[type="password"]') !== null;
      const isCloudflareChallenge = document.body.textContent.includes('Please wait while your request is being verified');
      const isCloudflareBlock = document.body.textContent.includes('Access denied') || 
                               document.body.textContent.includes('Error 1020');
      
      return {
        hasLoginForm: document.querySelector('form') !== null,
        hasEmailInput: document.querySelector('input[type="email"]') !== null,
        hasPasswordInput: document.querySelector('input[type="password"]') !== null,
        hasSubmitButton: document.querySelector('button[type="submit"]') !== null,
        bodyClasses: document.body.className,
        hasStyles: document.querySelectorAll('link[rel="stylesheet"]').length > 0,
        isLoginPage,
        isCloudflareChallenge,
        isCloudflareBlock,
        pageTitle: document.title,
        firstText: document.body.textContent.substring(0, 200)
      };
    });
    
    console.log('\nPage analysis:');
    console.log('- Is actual login page:', pageContent.isLoginPage);
    console.log('- Is Cloudflare challenge:', pageContent.isCloudflareChallenge);
    console.log('- Is Cloudflare block:', pageContent.isCloudflareBlock);
    console.log('- Has login form:', pageContent.hasLoginForm);
    console.log('- Has email input:', pageContent.hasEmailInput); 
    console.log('- Has password input:', pageContent.hasPasswordInput);
    console.log('- Has submit button:', pageContent.hasSubmitButton);
    console.log('- Body classes:', pageContent.bodyClasses);
    console.log('- Has stylesheets:', pageContent.hasStyles);
    
    console.log('\nFirst 200 chars of page text:');
    console.log(pageContent.firstText);
    
    console.log('\nProduction page test completed!');
    
  } catch (error) {
    console.error('Production page error:', error.message);
  }
  
  await browser.close();
})();