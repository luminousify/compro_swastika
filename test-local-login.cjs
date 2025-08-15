const puppeteer = require('puppeteer');

(async () => {
  const browser = await puppeteer.launch({
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-web-security']
  });

  console.log('Testing Local Login Page (Fixed)...');
  const localPage = await browser.newPage();
  
  // Enable console logging
  localPage.on('console', msg => console.log('LOCAL CONSOLE:', msg.text()));
  localPage.on('pageerror', error => console.log('LOCAL PAGE ERROR:', error.message));
  
  // Track network requests
  const responses = [];
  localPage.on('response', response => {
    if (response.url().includes('.css') || response.url().includes('.js') || response.url().includes('build')) {
      responses.push({ 
        url: response.url(), 
        status: response.status(),
        contentType: response.headers()['content-type'] || 'unknown'
      });
    }
  });
  
  try {
    await localPage.goto('http://127.0.0.1:8000/login', { 
      waitUntil: 'networkidle2',
      timeout: 30000 
    });
    
    await localPage.screenshot({ 
      path: 'local-login-fixed-screenshot.png',
      fullPage: true 
    });
    
    console.log('\nNetwork requests for CSS/JS/Build files:');
    responses.forEach(r => console.log('-', r.url, '(Status:', r.status, ', Type:', r.contentType, ')'));
    
    // Check if the page contains expected elements
    const titleText = await localPage.title();
    console.log('\nPage title:', titleText);
    
    const loginElements = await localPage.evaluate(() => {
      return {
        hasLoginForm: document.querySelector('form') !== null,
        hasEmailInput: document.querySelector('input[type="email"]') !== null,
        hasPasswordInput: document.querySelector('input[type="password"]') !== null,
        hasSubmitButton: document.querySelector('button[type="submit"]') !== null,
        bodyClasses: document.body.className,
        hasStyles: document.querySelectorAll('link[rel="stylesheet"]').length > 0,
        pageHTML: document.documentElement.outerHTML.substring(0, 500)
      };
    });
    
    console.log('\nPage analysis:');
    console.log('- Has login form:', loginElements.hasLoginForm);
    console.log('- Has email input:', loginElements.hasEmailInput); 
    console.log('- Has password input:', loginElements.hasPasswordInput);
    console.log('- Has submit button:', loginElements.hasSubmitButton);
    console.log('- Body classes:', loginElements.bodyClasses);
    console.log('- Has stylesheets:', loginElements.hasStyles);
    
    console.log('\nFirst 500 chars of HTML:');
    console.log(loginElements.pageHTML);
    
    console.log('\nLocal page loaded successfully!');
    
  } catch (error) {
    console.error('Local page error:', error.message);
  }
  
  await browser.close();
})();