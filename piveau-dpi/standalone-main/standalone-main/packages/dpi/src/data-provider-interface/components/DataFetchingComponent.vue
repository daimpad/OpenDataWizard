<template>
  <div style="height: 370px">
    <div class="spinner"></div>
  </div>
</template>

<script>
import { mapActions, mapGetters } from "vuex";

export default {
  props: ["id", "property", "catalog"],
  data() {
    return {};
  },
  computed: {
    ...mapGetters("auth", ["getIsDraft", "getUserData"]),
    token() {
      return this.getUserData.rtpToken;
    },
    redirectUri() {
      return `${this.$env.content.dataProviderInterface.basePath}/${this.property}?locale=${this.$i18n.locale}`;
    },
  },
  methods: {
    ...mapActions("auth", ["setIsEditMode", "setIsDraft"]),
    ...mapActions("dpiStore", ["convertToInput"]),
    async setupEditPage() {
      this.$router.push({
        name: "DataProviderInterface-Input",
        params: { property: this.property },
        query: {
          locale: this.$route.query.locale,
          edit: true,
          id:
            !this.getIsDraft && this.property === "catalogues"
              ? this.catalog
              : this.id,
          catalog: this.catalog,
          fromDraft: this.getIsDraft ? "true" : undefined,
        },
      });

      return;

      let endpoint;
      this.setIsEditMode(true);
      const specification =
        this.$env.content.dataProviderInterface.specification;

      if (this.getIsDraft) {
        this.setIsDraft(true);
        endpoint = `${this.$env.api.hubUrl}drafts/datasets/${this.id}.nt?catalogue=${this.catalog}`;
        await this.convertToInput({
          endpoint,
          token: this.token,
          property: this.property,
          id: this.id,
          specification: specification,
        });
      } else {
        this.setIsDraft(false);
        if (this.property === "catalogues") {
          endpoint = `${this.$env.api.hubUrl}catalogues/${this.catalog}.nt`;
          await this.convertToInput({
            endpoint,
            token: this.token,
            property: this.property,
            id: this.catalog,
            specification: specification,
          });
        } else {
          endpoint = `${this.$env.api.hubUrl}datasets/${this.id}.nt?useNormalizedId=true`;
          await this.convertToInput({
            endpoint,
            token: this.token,
            property: this.property,
            id: this.id,
            specification: specification,
          });
        }
      }

      this.$router.push(this.redirectUri).catch(() => {});
    },
  },
  beforeRouteEnter(to, from, next) {
    next((vm) => {
      if (from.fullPath.startsWith(vm.redirectUri)) {
        vm.$router.go(-1);
      } else vm.setupEditPage();
    });
  },
};
</script>
