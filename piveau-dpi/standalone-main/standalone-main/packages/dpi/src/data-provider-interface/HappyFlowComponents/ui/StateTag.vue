<template>
  <div class="state-tag" :class="[stateClass, sizeClass]">
    <img
      v-if="state === 'published'"
      class="dot"
      src="../img/Ellipse2Published.svg"
      alt=""
    />
    <img
      v-if="state === 'draft'"
      class="dot"
      src="../img/Ellipse2Warning.svg"
      alt=""
    />
    <img
      v-if="state === 'error'"
      class="dot"
      src="../img/Ellipse2Error.svg"
      alt=""
    />

    <span class="label">{{ label }}</span>
  </div>
</template>

<script>
import "../../config/styles/variables.css";
import "../../config/styles/typography.css";

export default {
  name: "StateTag",
  props: {
    label: {
      type: String,
      required: true,
    },
    state: {
      type: String,
      required: true, // "published", "draft", "error", "hvd", "geopolitical"
    },
    size: {
      type: String,
      default: "table", // "table", "page", or other custom sizes
    },
  },
  computed: {
    stateClass() {
      return `state-tag--${this.state}`;
    },
    sizeClass() {
      return `state-tag--${this.size}`;
    },
  },
};
</script>

<style scoped>
.state-tag {
  font-size: 14px;
  font-weight: 500;
  display: inline-flex;
  padding: var(--Spacing-1, 4px) var(--Spacing-2, 8px);
  align-items: center;
  gap: var(--Spacing-1, 4px);
  border-radius: var(--Button-Radius, 24px);;
  background: var(--background-success, #c2eaaf);
  color: var(--text-success, #3d6e26);
}

.state-tag--published {
  background: var(--background-success, #c2eaaf);
  color: var(--text-success, #3d6e26);

  font-family: var(--font-family-secondary);
  font-size: var(--copy-mini-semi-bold-font-size);
  font-style: normal;
  font-weight: var(--copy-mini-semi-bold-font-weight);
  line-height: var(--copy-mini-semi-bold-line-height);
}

.state-tag--draft {
  border-radius: var(--Button-Radius, 24px);;
  background: var(--background-warning, #ffd7a7);

  font-family: var(--font-family-secondary);
  font-size: var(--copy-mini-semi-bold-font-size);
  font-style: normal;
  font-weight: var(--copy-mini-semi-bold-font-weight);
  line-height: var(--copy-mini-semi-bold-line-height);
  color: var(--text-warning, #7c501c);
}

.state-tag--error {
  border-radius: var(--Button-Radius, 24px);;
  background: var(--background-error, #ffc7cb);
  font-family: var(--font-family-secondary);
  font-size: var(--copy-mini-semi-bold-font-size);
  font-style: normal;
  font-weight: var(--copy-mini-semi-bold-font-weight);
  line-height: var(--copy-mini-semi-bold-line-height);
  color: var(--text-error, #a9242c);
}

.dot {
  display: inline-block;
  width: 6px;
  height: 6px;
}

.state-tag--page {
  padding: var(--Spacing-1, 4px) var(--Spacing-3, 16px);
  align-items: center;
  gap: var(--Spacing-1, 4px);
  font-family: var(--font-family-secondary);;
  font-size: var(--copy-small-semi-bold-font-size);
  font-style: normal;
  font-weight: var(--copy-small-semi-bold-font-weight, 600);
  line-height: var(--copy-small-semi-bold-line-height);
}

.state-tag--hvd {
  font-family: var(--font-family-secondary);
  display: inline-flex;
  padding: var(--Spacing-1, 4px) var(--Spacing-2, 8px);
  align-items: center;
  background: var(--hvd-background);
  font-style: normal;
  color: var(--hvd-text);
  font-size: var(--caption-font-size, 0.75rem);
  line-height: var(--caption-line-height, 1.125rem);
  font-weight: var(--caption-font-weight, 700);
  border-radius: var(--Border-Radius, 8px);
  text-transform: uppercase;
}

.state-tag--geopolitical {
  display: inline-flex;
  padding: var(--Spacing-1, 4px) var(--Spacing-2, 8px);
  align-items: center;
  background: var(--blue-20, #D4EDFC);
  font-style: normal;
  color: var(--Colour-blue-Blue90, #015184);
  font-size: var(--caption-font-size, 0.75rem);
  line-height: var(--caption-line-height, 1.125rem);
  font-weight: var(--caption-font-weight, 700);
  border-radius: var(--Border-Radius, 8px);
}
</style>
