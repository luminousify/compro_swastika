const puppeteer = require('puppeteer');

async function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

(async () => {
  console.log('🔍 Debug Testing Image Sliders...\n');
  
  const browser = await puppeteer.launch({ headless: false, devtools: true });
  const page = await browser.newPage();
  
  // Capture console messages
  const consoleMessages = [];
  page.on('console', msg => {
    const type = msg.type();
    const text = msg.text();
    consoleMessages.push(`${type}: ${text}`);
    console.log(`[BROWSER] ${type}: ${text}`);
  });
  
  // Capture errors
  page.on('pageerror', error => {
    console.log(`[BROWSER ERROR] ${error.message}`);
  });
  
  try {
    console.log('📍 Navigating to Adhesive division...');
    await page.goto('http://localhost:8000/divisions/adhesive', { 
      waitUntil: 'domcontentloaded' 
    });
    
    console.log('⏳ Waiting for JavaScript to load...');
    await sleep(8000); // Wait longer for everything to initialize
    
    // Check what's happening
    const debugInfo = await page.evaluate(() => {
      return {
        swiperLoaded: typeof window.Swiper !== 'undefined',
        hasNavigation: typeof window.Swiper?.Navigation !== 'undefined',
        hasPagination: typeof window.Swiper?.Pagination !== 'undefined',
        sliderElements: document.querySelectorAll('.swiper-container').length,
        domReady: document.readyState
      };
    });
    
    console.log('\n🔍 Debug Information:');
    console.log('   DOM Ready State:', debugInfo.domReady);
    console.log('   Swiper Available:', debugInfo.swiperLoaded);
    console.log('   Navigation Module:', debugInfo.hasNavigation);
    console.log('   Pagination Module:', debugInfo.hasPagination);
    console.log('   Slider Elements:', debugInfo.sliderElements);
    
    console.log('\n📝 All Console Messages:');
    if (consoleMessages.length === 0) {
      console.log('   (No console messages captured)');
    } else {
      consoleMessages.forEach(msg => console.log(`   ${msg}`));
    }
    
    console.log('\n🔍 Keeping browser open for manual inspection...');
    console.log('   Check the browser console and examine the sliders manually.');
    console.log('   Press Ctrl+C when done.');
    
    // Keep browser open
    await new Promise(() => {});
    
  } catch (error) {
    console.error('❌ Test failed:', error.message);
  } finally {
    // Don't close browser automatically
  }
})();