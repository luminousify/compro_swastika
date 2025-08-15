import puppeteer from 'puppeteer';

async function testImageDisplay() {
  const browser = await puppeteer.launch({ 
    headless: false,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });
  
  const page = await browser.newPage();
  
  // Listen for console messages and network failures
  const consoleMessages = [];
  const networkErrors = [];
  
  page.on('console', (msg) => {
    consoleMessages.push({
      type: msg.type(),
      text: msg.text()
    });
    console.log(`Console ${msg.type()}: ${msg.text()}`);
  });
  
  page.on('requestfailed', (request) => {
    networkErrors.push({
      url: request.url(),
      errorText: request.failure().errorText
    });
    console.log(`Request failed: ${request.url()} - ${request.failure().errorText}`);
  });
  
  page.on('response', (response) => {
    if (response.status() >= 400) {
      console.log(`HTTP Error: ${response.url()} - Status: ${response.status()}`);
      networkErrors.push({
        url: response.url(),
        status: response.status(),
        statusText: response.statusText()
      });
    }
  });
  
  try {
    console.log('Navigating to line-of-business page...');
    await page.goto('http://localhost:8000/line-of-business', { 
      waitUntil: 'networkidle2',
      timeout: 30000 
    });
    
    console.log('Page loaded, checking for images...');
    
    // Wait for any images to load
    await new Promise(resolve => setTimeout(resolve, 3000));
    
    // Find all division images
    const images = await page.evaluate(() => {
      const imgElements = Array.from(document.querySelectorAll('img'));
      return imgElements.map(img => ({
        src: img.src,
        alt: img.alt || 'No alt text',
        naturalWidth: img.naturalWidth,
        naturalHeight: img.naturalHeight,
        complete: img.complete,
        className: img.className
      }));
    });
    
    console.log('\n=== IMAGE ANALYSIS ===');
    console.log(`Found ${images.length} images on the page:`);
    
    images.forEach((img, index) => {
      console.log(`\nImage ${index + 1}:`);
      console.log(`  Source: ${img.src}`);
      console.log(`  Alt: ${img.alt}`);
      console.log(`  Dimensions: ${img.naturalWidth}x${img.naturalHeight}`);
      console.log(`  Loaded: ${img.complete && img.naturalWidth > 0 ? 'YES' : 'NO'}`);
      console.log(`  Classes: ${img.className}`);
    });
    
    // Test specific image mentioned in the request
    const specificImageUrl = 'http://localhost:8000/storage/media/division/2025/08/d48ba2c4d34527ccebf3fe2381b667a5.jpg';
    console.log(`\n=== TESTING SPECIFIC IMAGE ===`);
    console.log(`Testing: ${specificImageUrl}`);
    
    try {
      const response = await page.goto(specificImageUrl);
      console.log(`Status: ${response.status()} - ${response.statusText()}`);
      if (response.status() === 200) {
        console.log('✅ Specific image loads successfully!');
      } else {
        console.log('❌ Specific image failed to load');
      }
    } catch (error) {
      console.log(`❌ Error loading specific image: ${error.message}`);
    }
    
    // Go back to the line-of-business page
    await page.goto('http://localhost:8000/line-of-business', { waitUntil: 'networkidle2' });
    
    // Take a screenshot
    console.log('\n=== TAKING SCREENSHOT ===');
    await page.screenshot({ 
      path: 'C:\\laragon\\www\\compro_swastika\\line-of-business-test.png', 
      fullPage: true 
    });
    console.log('Screenshot saved as line-of-business-test.png');
    
    // Summary
    console.log('\n=== SUMMARY ===');
    const loadedImages = images.filter(img => img.complete && img.naturalWidth > 0);
    const failedImages = images.filter(img => !img.complete || img.naturalWidth === 0);
    
    console.log(`Total images: ${images.length}`);
    console.log(`Successfully loaded: ${loadedImages.length}`);
    console.log(`Failed to load: ${failedImages.length}`);
    
    if (networkErrors.length > 0) {
      console.log(`\nNetwork errors (${networkErrors.length}):`);
      networkErrors.forEach(error => {
        console.log(`  - ${error.url}: ${error.status || error.errorText}`);
      });
    } else {
      console.log('\n✅ No network errors detected!');
    }
    
    if (consoleMessages.some(msg => msg.type === 'error')) {
      console.log('\nConsole errors detected:');
      consoleMessages.filter(msg => msg.type === 'error').forEach(msg => {
        console.log(`  - ${msg.text}`);
      });
    } else {
      console.log('\n✅ No console errors detected!');
    }
    
  } catch (error) {
    console.error('Test failed:', error.message);
  } finally {
    await browser.close();
  }
}

testImageDisplay().catch(console.error);