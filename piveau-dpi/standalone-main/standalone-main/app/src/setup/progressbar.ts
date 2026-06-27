import VueProgressBar from "@aacassandra/vue3-progressbar";
import { App } from "vue";

export function setupProgressbar(app: App) {
  const progressBarOptions = {
    thickness: '5px',
    autoRevert: false,
    transition: {
      speed: '1.0s',
      opacity: '0.5s',
      termination: 1000,
    },
  };

  app.use(VueProgressBar, progressBarOptions);
}