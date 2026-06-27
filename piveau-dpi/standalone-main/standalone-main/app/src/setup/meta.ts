import { App } from 'vue';
import { createHead } from '@unhead/vue';

export function setupMeta(app: App) {
  const head = createHead();
  app.use(head);

  return head;
}