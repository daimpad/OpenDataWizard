import { App } from 'vue';
import { Skeletor } from 'vue-skeletor';
import 'vue-skeletor/dist/vue-skeletor.css';

export function setupSkeletor(app: App) {
  app.component(Skeletor.name, Skeletor);
}