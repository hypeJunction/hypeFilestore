import { test, expect } from '@playwright/test';

/**
 * E2E smoke for hypefilestore.
 *
 * Service-only plugin: registers an entity:icon:url hook handler in
 * elgg-plugin.php so file/icon URLs route through the plugin's
 * IconServer. No actions, no routes, no view extensions. Smoke surface:
 *   - homepage activates without fataling
 *   - login page renders (exercises entity:icon:url hook on site logo)
 *   - elgg.css aggregate compiles (catches activation breaking the
 *     simplecache pipeline)
 */
test.describe('hypefilestore', () => {
  test('homepage renders with no PHP fatal markers', async ({ page }) => {
    const response = await page.goto('/');
    expect(response).toBeTruthy();
    expect(response!.status()).toBeLessThan(500);
    const body = await page.content();
    expect(body).not.toContain('Fatal error');
    expect(body).not.toContain('Uncaught');
    expect(body).not.toContain('ParseError');
  });

  test('login page exercises entity:icon:url hook without fatal', async ({ page }) => {
    const response = await page.goto('/login');
    expect(response).toBeTruthy();
    expect(response!.status()).toBeLessThan(500);
    const body = await page.content();
    expect(body).not.toContain('Fatal error');
    expect(body).not.toContain('Uncaught');
  });

  test('default css aggregate compiles', async ({ page }) => {
    const response = await page.goto('/cache/0/default/elgg.css');
    expect(response).toBeTruthy();
    if (response!.status() !== 404) {
      expect(response!.status()).toBeLessThan(400);
      expect(response!.headers()['content-type'] || '').toMatch(/css|text/);
    }
  });
});
