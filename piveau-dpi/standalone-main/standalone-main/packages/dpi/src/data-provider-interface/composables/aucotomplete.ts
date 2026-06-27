import { computed, inject, type InjectionKey, toValue } from "vue";
import axios from "axios";
import { type ComputedDpiContext } from "./useDpiContext";
import { type ResolvedConfig } from "../config-schema";

export interface AutocompleteInstance {
  requestFirstEntrySuggestions(voc: string, base: string): Promise<any>;
  requestAutocompleteSuggestions(options: {
    voc: string;
    text: string;
    base: string;
  }): Promise<any>;
  requestResourceName(options: {
    voc: string;
    uri: string;
    envs: any;
  }): Promise<any>;
}

export interface AutocompleteOptions {
  name: string;
  adapter: AutocompleteInstance;
}

export const autocompleteKey = Symbol(
  "autocomplete"
) as InjectionKey<AutocompleteInstance>;

export function defaultAutocompleteAdapter(options: {
  envs: ResolvedConfig
  dpiContext: ComputedDpiContext
}): AutocompleteOptions {
  const { envs, dpiContext } = options
  
  return {
    name: "default",
    adapter: {
      requestFirstEntrySuggestions: async (voc, base) => {
        return axios.get(`${base}search?filter=vocabulary&vocabulary=${voc}`);
      },
      requestAutocompleteSuggestions: async (options) => {
        return axios.get(
          `${options.base}search?filter=vocabulary&vocabulary=${options.voc}&q=${options.text}&limit=14`
        );
      },
      requestResourceName: async (options) => {
        const { voc, uri } = options;
        const specification = computed(
          () => toValue(dpiContext)?.specification
        );

        // // Catching invalid URIs
        if (voc === undefined) return;
        if (voc === "application") return;

        let req = "";

        // vocabularies for spdx checksum and inana-media-types are structured differently in the backend then other vocabularies
        if (voc === "iana-media-types" || voc === "spdx-checksum-algorithm") {
          req = `${envs.api.baseUrl}vocabularies/${voc}`;
        } else {
          const value = uri.replace(
            (specification.value.vocabPrefixes as any)[voc],
            ""
          );
          const valueEncoded = encodeURIComponent(value);
          const requestByUri = value !== valueEncoded;
          req = !requestByUri
            ? `${envs.api.baseUrl}vocabularies/${voc}/${valueEncoded}`
            : `${
                envs.api.baseUrl
              }vocabularies/${voc}/vocable?resource=${encodeURIComponent(uri)}`;
        }
        return axios.get(req);
      },
    },
  };
}

export function useAutocomplete() {
  const autocomplete = inject(autocompleteKey);

  if (!autocomplete) {
    throw new Error(
      "[useAutocomplete] Autocomplete not found. Did you forget to inject it?"
    );
  }

  return autocomplete;
}
