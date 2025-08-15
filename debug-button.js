import puppeteer from 'puppeteer';

(async () => {
  const browser = await puppeteer.launch({
    headless: false,
    defaultViewport: { width: 1920, height: 1080 }
  });

  const page = await browser.newPage();
  
  try {
    console.log('🔍 Debugging Add Product button on production...');

    // Login
    await page.goto('https://dayaswastika.com/login', { waitUntil: 'networkidle0' });
    await page.type('input[name="email"]', 'admin@dsp.co.id');
    await page.type('input[name="password"]', 'password');
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle0' }),
      page.click('button[type="submit"]')
    ]);

    // Go to division page
    await page.goto('https://dayaswastika.com/admin/divisions/adhesive', { waitUntil: 'networkidle0' });

    // Extract the exact HTML around the Add Product button
    const buttonHTML = await page.evaluate(() => {
      // Get all elements that might be the Add Product button
      const potentialButtons = Array.from(document.querySelectorAll('*')).filter(el => {
        const text = el.textContent.trim().toLowerCase();
        return text.includes('add product');
      });

      return potentialButtons.map(btn => ({
        tagName: btn.tagName,
        innerHTML: btn.innerHTML,
        outerHTML: btn.outerHTML,
        textContent: btn.textContent.trim(),
        href: btn.href || null,
        onclick: btn.onclick ? btn.onclick.toString() : null,
        attributes: Array.from(btn.attributes).map(attr => ({ name: attr.name, value: attr.value }))
      }));
    });

    console.log('🔍 Add Product button HTML details:');
    console.log(JSON.stringify(buttonHTML, null, 2));

    // Check if there are any route generation issues
    const routeCheck = await page.evaluate(() => {
      // Look for Laravel route helper or any relevant JavaScript
      return {
        hasLaravelMix: typeof window.Laravel !== 'undefined',
        hasAxios: typeof window.axios !== 'undefined',
        currentURL: window.location.href,
        baseURL: document.querySelector('meta[name="base-url"]')?.content || null,
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || null
      };
    });

    console.log('🔍 Page environment:');
    console.log(JSON.stringify(routeCheck, null, 2));

    // Check the Products tab specifically
    const productsTabHTML = await page.evaluate(() => {
      const productsContent = document.getElementById('products-content');
      return productsContent ? productsContent.innerHTML : null;
    });

    console.log('🔍 Products tab content:');
    console.log(productsTabHTML?.substring(0, 1000) + '...');

  } catch (error) {
    console.error('❌ Debug failed:', error.message);
  } finally {
    await browser.close();
  }
})();