const puppeteer = require('puppeteer');

async function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

(async () => {
  console.log('🔍 Testing Image Sliders...\n');
  
  const browser = await puppeteer.launch({ headless: false });
  const page = await browser.newPage();
  
  try {
    console.log('📍 Navigating to Adhesive division...');
    await page.goto('http://localhost:8000/divisions/adhesive', { waitUntil: 'domcontentloaded' });
    await sleep(5000); // Wait for JavaScript to load and initialize
    
    // Take screenshot
    await page.screenshot({ path: 'slider-test.png', fullPage: true });
    console.log('📸 Screenshot saved: slider-test.png');
    
    // Check sliders
    const results = await page.evaluate(() => {
      const sliders = document.querySelectorAll('.swiper-container');
      return {
        swiperLoaded: typeof window.Swiper !== 'undefined',
        sliderCount: sliders.length,
        sliders: Array.from(sliders).map(slider => ({
          id: slider.id,
          initialized: slider.classList.contains('swiper-initialized'),
          slideCount: slider.querySelectorAll('.swiper-slide').length,
          hasNavigation: !!(slider.querySelector('.swiper-button-next')),
          hasPagination: !!slider.querySelector('.swiper-pagination')
        }))
      };
    });
    
    console.log('\n✅ Test Results:');
    console.log(`   Swiper loaded: ${results.swiperLoaded}`);
    console.log(`   Number of sliders: ${results.sliderCount}`);
    
    results.sliders.forEach((slider, index) => {
      console.log(`\n   Slider ${index + 1}: ${slider.id}`);
      console.log(`   - Initialized: ${slider.initialized}`);
      console.log(`   - Slides: ${slider.slideCount}`);
      console.log(`   - Navigation: ${slider.hasNavigation}`);
      console.log(`   - Pagination: ${slider.hasPagination}`);
    });
    
    const allWorking = results.swiperLoaded && results.sliders.every(s => 
      s.initialized && s.slideCount > 1 && s.hasNavigation && s.hasPagination
    );
    
    console.log(`\n🎯 Final Result: ${allWorking ? '✅ WORKING' : '❌ NOT WORKING'}`);
    
    await sleep(3000);
    await browser.close();
    
  } catch (error) {
    console.error('❌ Test failed:', error.message);
    await browser.close();
  }
})();