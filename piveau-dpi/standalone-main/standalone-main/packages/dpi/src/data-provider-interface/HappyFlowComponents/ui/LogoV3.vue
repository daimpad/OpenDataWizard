<template>
  <div class="dpiV3_logo-container">
    <div class="dpiV3_logo">
      <img
        src="../img/bydata_logo_blue_small.svg"
        alt="Logo"
        class="dpiV3_logo-image"
      />
    </div>

    <div class="dpiV3_text">
      <img
        class="dpiV3_fur-svg"
        src="../img/FurV3.svg"
        width="25px"
        height="14px"
        fill="none"
      />

      <img
        class="dpiV3_datenbereitsteller-svg"
        src="../img/DatenbereitstellerV3.svg"
        width="156"
        height="14"
        fill="none"
      />
    </div>
  </div>
</template>

<script>
export default {
  name: "LogoComponent",
};
</script>

<style scoped>
.dpiV3_logo-container {
  display: inline-flex; 
  align-items: center;
  gap: var(--Spacing-3, 16px);
}

.dpiV3_logo {
  width: 48px;
  height: 48px;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-shrink: 0;
}

.dpiV3_text {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-2, 8px);
}

.dpiV3_logo-image {
  width: 48px;
  height: auto;
}

.dpiV3_datenbereitsteller-svg {
  height: 14px;
  width: auto;
}
</style>
