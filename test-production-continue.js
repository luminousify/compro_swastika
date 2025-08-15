import puppeteer from 'puppeteer';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

(async () => {
  const browser = await puppeteer.launch({
    headless: false,
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
    const filename = `continue-screenshot-${stepCounter}-${description.replace(/[^a-zA-Z0-9]/g, '-')}.png`;
    const filepath = path.join(__dirname, filename);
    await page.screenshot({ path: filepath, fullPage: true });
    screenshots.push({ step: stepCounter, description, filename, filepath });
    console.log(`📸 Screenshot ${stepCounter}: ${description} -> ${filename}`);
    stepCounter++;
  };

  try {
    console.log('🚀 Continuing production site test - starting from login...');

    // Step 1: Navigate to login page and login quickly
    console.log('Step 1: Logging in...');
    await page.goto('https://dayaswastika.com/login', { waitUntil: 'networkidle0', timeout: 30000 });
    
    // Fill in credentials
    await page.waitForSelector('input[name="email"]');
    await page.type('input[name="email"]', 'admin@dsp.co.id');
    await page.type('input[name="password"]', 'password');
    
    // Submit login form and wait for admin dashboard
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle0', timeout: 30000 }),
      page.click('button[type="submit"]')
    ]);
    
    console.log('✅ Login successful');
    await takeScreenshot('logged-in-dashboard');

    // Step 2: Navigate directly to divisions/adhesive page
    console.log('Step 2: Navigating to adhesive division page...');
    await page.goto('https://dayaswastika.com/admin/divisions/adhesive', { waitUntil: 'networkidle0', timeout: 30000 });
    await takeScreenshot('adhesive-division-page');

    // Step 3: Look for Add Product button with comprehensive search
    console.log('Step 3: Searching for Add Product button...');
    
    // Get all the text on the page to see what's available
    const pageText = await page.evaluate(() => document.body.innerText);
    console.log('Page contains text about products:', pageText.includes('Product') || pageText.includes('product'));
    
    // Look for various button/link patterns
    const buttonSearch = await page.evaluate(() => {
      const elements = Array.from(document.querySelectorAll('a, button, .btn, [role="button"]'));
      const results = [];
      
      elements.forEach((el, index) => {
        const text = el.textContent.trim().toLowerCase();
        const href = el.href || '';
        const className = el.className || '';
        
        // Look for product-related buttons
        if (text.includes('product') || text.includes('add') || text.includes('create') || text.includes('new') ||
            href.includes('product') || href.includes('create') || className.includes('add') || className.includes('create')) {
          results.push({
            index,
            tagName: el.tagName,
            text: el.textContent.trim(),
            href,
            className,
            id: el.id,
            visible: window.getComputedStyle(el).display !== 'none'
          });
        }
      });
      
      return results;
    });
    
    console.log('Found potential product-related buttons:', buttonSearch);
    
    // Try to find the specific Add Product button
    let addProductButton = null;
    
    // First try direct text search
    addProductButton = await page.evaluateHandle(() => {
      const elements = Array.from(document.querySelectorAll('a, button'));
      return elements.find(el => {
        const text = el.textContent.trim().toLowerCase();
        return text.includes('add product') || text.includes('tambah produk') || 
               text.includes('create product') || text.includes('new product');
      });
    });
    
    // If not found, try href-based search
    if (!addProductButton || await addProductButton.evaluate(el => el === null)) {
      addProductButton = await page.evaluateHandle(() => {
        const elements = Array.from(document.querySelectorAll('a'));
        return elements.find(el => {
          const href = el.href || '';
          return href.includes('/products/create') || href.includes('product/create');
        });
      });
    }
    
    // Check if we found a button
    const buttonExists = addProductButton && await addProductButton.evaluate(el => el !== null);
    
    if (buttonExists) {
      console.log('✅ Add Product button found!');
      
      // Get button details
      const buttonDetails = await addProductButton.evaluate(el => ({
        tagName: el.tagName,
        text: el.textContent.trim(),
        href: el.href,
        className: el.className,
        id: el.id,
        disabled: el.disabled,
        visible: window.getComputedStyle(el).display !== 'none'
      }));
      
      console.log('Button details:', buttonDetails);
      
      // Highlight the button
      await addProductButton.evaluate(el => {
        el.style.border = '3px solid red';
        el.style.backgroundColor = 'yellow';
      });
      
      await takeScreenshot('button-highlighted');
      
      // Step 4: Try clicking the button
      console.log('Step 4: Clicking Add Product button...');
      
      const currentUrl = page.url();
      console.log('Current URL before click:', currentUrl);
      
      // Click and wait for potential navigation
      try {
        await Promise.all([
          page.waitForNavigation({ waitUntil: 'networkidle0', timeout: 10000 }).catch(() => console.log('No navigation occurred')),
          addProductButton.evaluate(el => el.click())
        ]);
      } catch (e) {
        console.log('Navigation timeout, but click executed');
      }
      
      await new Promise(resolve => setTimeout(resolve, 2000)); // Wait a bit more
      
      const newUrl = page.url();
      console.log('URL after click:', newUrl);
      
      await takeScreenshot('after-button-click');
      
      if (newUrl !== currentUrl) {
        console.log('✅ Button click caused navigation to:', newUrl);
        
        // Step 5: Test the create page if we navigated there
        if (newUrl.includes('create') || newUrl.includes('product')) {
          console.log('Step 5: Testing create product page...');
          
          // Check for form elements
          const formInfo = await page.evaluate(() => {
            const forms = document.querySelectorAll('form');
            const inputs = document.querySelectorAll('input, textarea, select');
            const requiredInputs = document.querySelectorAll('input[required], textarea[required], select[required]');
            const submitButtons = document.querySelectorAll('button[type="submit"], input[type="submit"]');
            
            return {
              formsCount: forms.length,
              inputsCount: inputs.length,
              requiredInputsCount: requiredInputs.length,
              submitButtonsCount: submitButtons.length,
              hasNameField: !!document.querySelector('input[name="name"], input[name="title"]'),
              hasDescriptionField: !!document.querySelector('textarea[name="description"]'),
              hasDivisionField: !!document.querySelector('select[name="division_id"], input[name="division_id"]')
            };
          });
          
          console.log('Create page form info:', formInfo);
          await takeScreenshot('create-form-analysis');
          
          // Try to fill out a basic form if it exists
          if (formInfo.hasNameField) {
            console.log('Attempting to test form functionality...');
            
            try {
              // Fill out basic fields
              const nameField = await page.$('input[name="name"], input[name="title"]');
              if (nameField) {
                await nameField.type('Test Product from Puppeteer');
              }
              
              const descField = await page.$('textarea[name="description"]');
              if (descField) {
                await descField.type('This is a test product created by automated testing.');
              }
              
              await takeScreenshot('form-filled');
              console.log('✅ Form fields filled successfully');
            } catch (e) {
              console.log('❌ Error filling form:', e.message);
            }
          }
        }
      } else {
        console.log('❌ Button click did not cause navigation - button might not be working');
        
        // Check for any JavaScript errors
        const errors = await page.evaluate(() => {
          return window.console.errors || [];
        });
        console.log('JavaScript errors after click:', errors);
      }
      
    } else {
      console.log('❌ Add Product button not found');
      
      // Let's examine the page structure more thoroughly
      const pageStructure = await page.evaluate(() => {
        const structure = {
          title: document.title,
          h1s: Array.from(document.querySelectorAll('h1')).map(h => h.textContent.trim()),
          h2s: Array.from(document.querySelectorAll('h2')).map(h => h.textContent.trim()),
          allButtons: Array.from(document.querySelectorAll('button, .btn, [role="button"]')).map(btn => ({
            text: btn.textContent.trim(),
            className: btn.className,
            href: btn.href || null
          })),
          allLinks: Array.from(document.querySelectorAll('a')).map(link => ({
            text: link.textContent.trim(),
            href: link.href,
            className: link.className
          })).filter(link => link.text.length > 0)
        };
        
        return structure;
      });
      
      console.log('Page structure analysis:');
      console.log('Title:', pageStructure.title);
      console.log('H1s:', pageStructure.h1s);
      console.log('H2s:', pageStructure.h2s);
      console.log('All buttons:', pageStructure.allButtons);
      console.log('All links:', pageStructure.allLinks);
    }

    await takeScreenshot('final-analysis');

  } catch (error) {
    console.error('❌ Test failed with error:', error.message);
    await takeScreenshot('final-error-state');
  } finally {
    console.log('\n📋 Test Summary:');
    console.log('Screenshots taken:', screenshots.length);
    screenshots.forEach(shot => {
      console.log(`  ${shot.step}. ${shot.description} -> ${shot.filename}`);
    });
    
    await browser.close();
  }
})();