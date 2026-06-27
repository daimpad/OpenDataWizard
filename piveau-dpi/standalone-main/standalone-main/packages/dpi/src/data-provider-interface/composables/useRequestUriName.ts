import { useStore } from "vuex";
import { useRuntimeEnv } from "../../composables/useRuntimeEnv";
import { getTranslationFor } from "../../utils/helpers";
import { type MaybeRefOrGetter, toValue } from "vue";
import { useAsyncState } from "@vueuse/core";
import { useAutocomplete } from "./aucotomplete";

/**
 * Fetches the name of a resource based on its URI, vocabulary, and property.
 */
export function useRequestUriName(options: {
  res: MaybeRefOrGetter<string>;
  voc: MaybeRefOrGetter<string>;
  property: MaybeRefOrGetter<string>;
  locale: MaybeRefOrGetter<string>;
}) {
  const {
    res: _res,
    voc: _voc,
    property: _property,
    locale: _locale,
  } = options;

  const envs = useRuntimeEnv();
  const store = useStore();
  const { requestResourceName } = useAutocomplete()

  const request = useAsyncState(
    async () => {
      const res = toValue(_res);
      const voc = toValue(_voc);
      const property = toValue(_property);
      const locale = _locale;

      if (res != undefined) {
        let vocMatch =
          voc === "iana-media-types" || voc === "spdx-checksum-algorithm";

        let name;

        await requestResourceName({
            voc: voc,
            uri: res,
            envs,
          })
          .then((response) => {
            if (property === "dcatde:politicalGeocodingURI") {
              if (response != undefined) {
                let result = vocMatch
                  ? response.data.result.results
                      .filter((dataset: any) => dataset.resource === res)
                      .map((dataset: any) =>
                        getTranslationFor(
                          dataset.alt_label,
                          toValue(locale),
                          []
                        )
                      )[0]
                  : getTranslationFor(
                      response.data.result.alt_label,
                      toValue(locale),
                      []
                    );
                name = result;
              }
            } else {
              if (response != undefined) {
                let result = vocMatch
                  ? response.data.result.results
                      .filter((dataset: any) => dataset.resource === res)
                      .map((dataset: any) =>
                        getTranslationFor(
                          dataset.pref_label,
                          toValue(locale),
                          []
                        )
                      )[0]
                  : getTranslationFor(
                      response.data.result.pref_label,
                      toValue(locale),
                      []
                    );
                name = result;
              }
            }
          });

        return name;
      }
    },
    undefined,
    { immediate: false }
  );

  return {
    ...request,
  };
}
