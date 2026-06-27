<template>
  <div class="dpiV3_illustration-wrapper">
    <img v-if="illustrationName === 'celebrate'"
      class="dpiV3_illustration-celebrate-svg"
      src="../img/IllustrationCelebrate.svg"
      alt="Celebrate Illustration"
    />

    <img v-if="illustrationName === 'start'"
      class="dpiV3_illustration-start-svg"
      src="../img/IllustrationStart.svg"
      alt="Start Illustration"
    />
  </div>
</template>

<script setup>
const props = defineProps({
  illustrationName: {
    type: String,
    default: "celebrate",
  },
});
</script>

<style scoped>
.dpiV3_illustration-wrapper {
  display: flex;
  gap: var(--Spacing-3, 10px);
}

.dpiV3_illustration-celebrate-svg {
  display: flex;
  width: 260.002px;
  height: 268px;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

.dpiV3_illustration-start-svg {
  display: flex;
  width: 286.001px;
  height: 252.001px;
  justify-content: center;
  align-items: center;
}
</style>
