<!-- Todo: split all the components into subcomponents  // also use i18n to handle the translations -->
<template>
    <!-- Todo: Added font for DPI development, remove after development -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />

    <link
      href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
      rel="stylesheet"
    />

    <div class="homeWrapper">
      <header class="d-flex align-items-center">
        <div class="DPIHeadL">
          <img
            class="w-50"
            src="https://equalengineers.com/wp-content/uploads/2024/04/dummy-logo-5b.png"
            alt="dpiLogo"
          />
        </div>
        <div class="DPIHeadR">
          <button>Datensätze</button>
          <button>FAQ</button>
        </div>
      </header>
      <main>
        <!-- Review Area for Components -->

        <div class="m-3">
          <h1>Review Area for Components</h1>
          <hr />
          <div class="preview m-3">
            <h4 class="m-3">Chip:</h4>
            <Chip
              class=""
              :text="'Chip Static'"
              :data="{ '@value': 'Chip Static', URI: 'https://Uri.com' }"
              :setup="{
                '@type': 'static',
                '@inTable': false,
                '@icon': 'suffix',
                '@search': true,
              }"
            ></Chip>
            <Chip
              class=""
              :text="'Leading Icon'"
              :data="{ '@value': 'Chip Unselected', URI: 'https://Uri.com' }"
              :setup="{
                '@type': 'select',
                '@icon': 'prefix',
                '@inTable': false,
              }"
            ></Chip>
            <Chip
              class=""
              :text="'Trailing Icon'"
              :data="{ '@value': 'Chip with cross', URI: 'https://Uri.com' }"
              :setup="{
                '@type': 'select',
                '@icon': 'suffix',
                '@inTable': false,
                '@selected': true,
              }"
            ></Chip>
            <Chip
              class=""
              :text="'Disabled'"
              :data="{ '@value': 'Chip with cross', URI: 'https://Uri.com' }"
              :setup="{ '@type': 'disabled', '@inTable': false }"
            ></Chip>
            <Chip
              class=""
              :text="'Disabled selected'"
              :data="{ '@value': 'Chip with cross', URI: 'https://Uri.com' }"
              :setup="{
                '@type': 'disabled',
                '@inTable': false,
                '@selected': true,
              }"
            ></Chip>
            <Chip
              class=""
              :text="'In Table'"
              :data="{ '@value': 'Chip in Table', URI: 'https://Uri.com' }"
              :setup="{
                '@type': 'static',
                '@inTable': true,
                '@icon': 'prefix',
                '@search': true,
              }"
            ></Chip>
            <Chip
              class=""
              :text="'In Table'"
              :data="{ '@value': 'Chip in Table', URI: 'https://Uri.com' }"
              :setup="{
                '@type': 'static',
                '@inTable': true,
                '@icon': 'suffix',
                '@search': true,
              }"
            ></Chip>
          </div>
          <hr />
          <div class="preview m-3">
            <h4 class="m-3">Dataset status</h4>
            <!-- <DatasetStatus timestamp="16 Okt 2023"/> Static -->
            <!-- Dynamic -->
            <DatasetStatus
              timestamp="01 Jan 2025"
              :statuses="[
                { label: 'Veröffentlicht', state: 'published' },
                { label: 'Entwurf', state: 'draft' },
              ]"
            />
          </div>
          <hr />
          <h3 class="m-4">Illustration</h3>
          <div class="preview m-5">
            <h4 class="m-5">Illustration celebrate:</h4>
            <Illustration illustrationName="celebrate" />
          </div>

          <div class="preview m-5">
            <h4 class="m-5">Illustration start:</h4>
            <Illustration illustrationName="start" />
          </div>
          <hr />
          <h3 class="m-4">Input field</h3>
          <div class="preview m-3">
            <h4 class="m-3">Input field all:</h4>
            <InputField
              :defaultInput="false"
              :addOnText="false"
              class="m-5"
              :eraseable="true"
              :datePicker="true"
              :infoIcon="true"
            ></InputField>
          </div>
          <div class="preview m-3">
            <h4 class="m-3">Input field:</h4>
            <InputField
              :addOnText="false"
              class="m-5"
              :datePicker="false"
              :infoIcon="false"
            ></InputField>
          </div>
          <div class="preview m-3">
            <h4 class="m-3">Input field leading text:</h4>
            <InputField
              :addOnText="true"
              class="m-5"
              :datePicker="false"
              :infoIcon="false"
            ></InputField>
          </div>
          <div class="preview m-3">
            <h4 class="m-3">Input field trailing text:</h4>
            <InputField
              :addOnText="true"
              :trailingText="true"
              class="m-5"
              :datePicker="false"
              :infoIcon="false"
            ></InputField>
          </div>
          <div class="preview m-3">
            <h4 class="m-3">Input field disabled:</h4>
            <InputField
              :isDisabled="true"
              :addOnText="false"
              class="m-5"
              :datePicker="false"
              :infoIcon="false"
            ></InputField>
          </div>
          <div class="preview m-3">
            <h4 class="m-3">Input field leading text disabled:</h4>
            <InputField
              :isDisabled="true"
              :addOnText="true"
              class="m-5"
              :datePicker="false"
              :infoIcon="false"
            ></InputField>
          </div>
          <div class="preview m-3">
            <h4 class="m-3">Input field trailing text disabled:</h4>
            <InputField
              :isDisabled="true"
              :addOnText="true"
              :trailingText="true"
              class="m-5"
              :datePicker="false"
              :infoIcon="false"
            ></InputField>
          </div>
          <div class="preview m-3">
            <h4 class="m-3">Input field error:</h4>
            <InputField
              :showError="true"
              :addOnText="false"
              class="m-5"
              :datePicker="false"
              :infoIcon="false"
            ></InputField>
          </div>
          <div class="preview m-3">
            <h4 class="m-3">Input field leading text error:</h4>
            <InputField
              :showError="true"
              :addOnText="true"
              class="m-5"
              :datePicker="false"
              :infoIcon="false"
            ></InputField>
          </div>
          <div class="preview m-3">
            <h4 class="m-3">Input field trailing text error:</h4>
            <InputField
              :showError="true"
              :addOnText="true"
              :trailingText="true"
              class="m-5"
              :datePicker="false"
              :infoIcon="false"
            ></InputField>
          </div>
          <div class="preview m-3">
            <h4 class="m-3">Text area:</h4>
            <TextAreaV3 :showError="false" class="m-5"></TextAreaV3>
          </div>
          <div class="preview m-3">
            <h4 class="m-3">Text area disabled:</h4>
            <TextAreaV3
              :isDisabled="true"
              :showError="false"
              class="m-5"
            ></TextAreaV3>
          </div>
          <div class="preview m-3">
            <h4 class="m-3">Text area error:</h4>
            <TextAreaV3 :showError="true" class="m-5"></TextAreaV3>
          </div>
          <hr />

          <div class="preview m-3">
            <h4 class="m-3">Button with tooltip:</h4>
            <ButtonV3
              size="medium"
              iconStart="MagnifyingGlass"
              variant="secondary"
              class="tooltipToggle"
              data-bs-toggle="tooltip"
              title="Look at me, I'm Mr Tooltip!"
            />
          </div>
          <hr />

          <div class="preview m-3">
            <h4 class="m-3">Textbutton mit Dropdown:</h4>
            <Dropdown
              buttonText="Dropdown"
              type="textButtonSmall"
              :text="{
                placeholder: 'URL Input',
                property: 'Label',
                support: 'This is a supporting message',
              }"
              :data="[
                { '@value': 'Eintrag 1', selected: false },
                { '@value': 'Eintrag 2', selected: false },
                { '@value': 'Eintrag 3', selected: false },
              ]"
            />
            <Dropdown
              buttonText="Dropdown Multi"
              type="textButtonSmall"
              :text="{
                placeholder: 'URL Input',
                property: 'Label',
                support: 'This is a supporting message',
              }"
              :data="[
                {
                  'Header 1': [
                    { '@value': 'Eintrag 1', selected: false },
                    { '@value': 'Eintrag 2', selected: false },
                    { '@value': 'Eintrag 3', selected: false },
                  ],
                },
                {
                  'Header 2': [{ '@value': 'Eintrag 1', selected: false }],
                },
                {
                  'Header 3': [
                    { '@value': 'Eintrag 1', selected: false },
                    { '@value': 'Eintrag 2', selected: false },
                    { '@value': 'Eintrag 3', selected: false },
                  ],
                },
              ]"
              multi="true"
            />
          </div>
          <div class="preview m-3">
            <h4 class="m-3">Dropdown inside input field:</h4>
            <Dropdown
              dropdownWidth="large"
              type="inputField"
              :inputFieldProps="{
                addOnText: false,
                initialHintText: false,
                datePicker: false,
                infoIcon: false,
                preIcon: false,
                label: 'HVD-Kategorie Test',
                dropdown_dpiV3: true,
                placeholder: 'TEST,  HVD-Kategorie',
                inputFieldSize: 'large',
              }"
              :data="[
                { '@value': 'Eintrag 1', selected: false },
                { '@value': 'Eintrag 2', selected: false },
                { '@value': 'Eintrag 3', selected: false },
                { '@value': 'Eintrag 4', selected: false },
                { '@value': 'Eintrag 5', selected: false },
                { '@value': 'Eintrag 6', selected: false },
              ]"
            />
          </div>
          <div class="preview m-3">
            <h4 class="m-3">Dropdown inside input field disabled:</h4>
            <Dropdown
              dropdownWidth="large"
              type="inputField"
              isDisabled="true"
              :inputFieldProps="{
                isDisabled: true,
                addOnText: false,
                initialHintText: false,
                datePicker: false,
                infoIcon: false,
                preIcon: false,
                label: 'HVD-Kategorie',
                dropdown_dpiV3: true,
                placeholder: 'Wählen Sie eine HVD-Kategorie',
                inputFieldSize: 'large',
              }"
              :data="[{ '@value': 'Eintrag 1', selected: false }]"
            />
          </div>

          <div class="preview m-3">
            <h4 class="m-3">Dropdown inside input field multi:</h4>
            <Dropdown
              dropdownWidth="large"
              type="inputField"
              :inputFieldProps="{
                addOnText: false,
                initialHintText: false,
                datePicker: false,
                infoIcon: false,
                preIcon: false,
                label: 'HVD-Kategorie',
                dropdown_dpiV3: true,
                placeholder: 'Wählen Sie eine HVD-Kategorie',
                inputFieldSize: 'large',
              }"
              :data="[
                {
                  'Header 1': [
                    { '@value': 'Header 1 Eintrag 1', selected: false },
                    { '@value': 'Header 1 Eintrag 2', selected: false },
                    { '@value': 'Header 1 Eintrag 3', selected: false },
                  ],
                },
                {
                  'Header 2': [{ '@value': 'Header 2 Eintrag 1', selected: false }],
                },
                {
                  'Header 3': [
                    { '@value': 'Header 3 Eintrag 1', selected: false },
                    { '@value': 'Header 3 Eintrag 2', selected: false },
                    { '@value': 'Header 3 Eintrag 3', selected: false },
                  ],
                },
              ]"
              multi="true"
            />
          </div>
          <hr />
          <div class="preview m-3">
            <h4 class="m-3">Checkboxes:</h4>
            <CheckboxV3 class="mx-3" :type="'checkbox'" :data="[]" state="idle">
            </CheckboxV3>
            <CheckboxV3 class="mx-3" :type="'radio'" :data="[]" state="neutral">
            </CheckboxV3>
            <CheckboxV3
              class="mx-3"
              :type="'radio'"
              :text="{ label: 'Label (disabled State)', subtext: '' }"
              :data="[]"
              state="disabled"
            >
            </CheckboxV3>
            <CheckboxV3
              class="mx-3"
              :type="'checkbox'"
              :text="{ label: 'Label (disabled State)', subtext: '' }"
              :data="[]"
              state="disabled"
            >
            </CheckboxV3>
            <CheckboxV3
              class="mx-3"
              :type="'checkbox'"
              :text="{ label: 'Label', subtext: 'Subtext' }"
              :data="[]"
              state="checked"
            >
            </CheckboxV3>
            <CheckboxV3
              class="mx-3"
              :type="'radio'"
              :text="{ label: 'Label', subtext: 'Subtext' }"
              :data="[]"
              state="checked"
            >
            </CheckboxV3>
          </div>
          <hr />
          <div class="preview m-3 d-block">
            <h4 class="m-3">Toast:</h4>
            <Toast :type="'error'" :text="{ value: 'Fehler' }" class="mb-3" />
            <Toast :type="'warning'" :text="{ value: 'Warnung' }" class="mb-3" />
            <Toast :type="'info'" :text="{ value: 'Info' }" class="mb-3" />
            <Toast :type="'success'" :text="{ value: 'Erfolg' }" class="mb-3" />
          </div>
          <hr />
          <h3 class="m-4">Primary button:</h3>
          <div class="buttonPreview m-3">
            <h4 class="m-3">Active:</h4>
            <ButtonV3
              @click="onButtonClick"
              buttonText="Button Small"
              size="small"
            />
            <ButtonV3 buttonText="Button Medium" size="medium" />
            <ButtonV3 buttonText="Button Large" size="large" />
          </div>
          <div class="preview m-3">
            <h4 class="m-3">Leading icon:</h4>
            <ButtonV3
              buttonText="Button Small"
              size="small"
              iconStart="MagnifyingGlass"
            />
            <ButtonV3
              buttonText="Button Medium"
              size="medium"
              iconStart="MagnifyingGlass"
            />
            <ButtonV3
              buttonText="Button Large"
              size="large"
              iconStart="MagnifyingGlass"
            />
          </div>
          <div class="preview m-3">
            <h4 class="m-3">Trailing icon:</h4>
            <ButtonV3
              buttonText="Button Small"
              size="small"
              iconEnd="MagnifyingGlass"
            />
            <ButtonV3
              buttonText="Button Medium"
              size="medium"
              iconEnd="MagnifyingGlass"
            />
            <ButtonV3
              buttonText="Button Large"
              size="large"
              iconEnd="MagnifyingGlass"
            />
          </div>
          <div class="preview m-3">
            <h4 class="m-3">Icon only:</h4>
            <ButtonV3 class="mx-3" size="small" iconStart="MagnifyingGlass" />
            <ButtonV3 class="mx-3" size="medium" iconStart="MagnifyingGlass" />
            <ButtonV3 class="mx-3" size="large" iconStart="MagnifyingGlass" />
          </div>
          <div class="preview m-3">
            <h4 class="m-3">Disabled:</h4>
            <ButtonV3 buttonText="Button Small" size="small" disabled />
            <ButtonV3 buttonText="Button Medium" size="medium" disabled />
            <ButtonV3 buttonText="Button Large" size="large" disabled />
          </div>
          <hr />
          <h3 class="m-4">Secondary button:</h3>
          <div class="buttonPreview m-3">
            <h4 class="m-3">Active:</h4>
            <ButtonV3
              buttonText="Button Small"
              size="small"
              variant="secondary"
            />
            <ButtonV3
              buttonText="Button Medium"
              size="medium"
              variant="secondary"
            />
            <ButtonV3
              buttonText="Button Large"
              size="large"
              variant="secondary"
            />
          </div>
          <div class="buttonPreview m-3">
            <h4 class="m-3">Leading icon:</h4>
            <ButtonV3
              buttonText="Button Small"
              size="small"
              iconStart="MagnifyingGlass"
              variant="secondary"
            />
            <ButtonV3
              buttonText="Button Medium"
              size="medium"
              iconStart="MagnifyingGlass"
              variant="secondary"
            />
            <ButtonV3
              buttonText="Button Large"
              size="large"
              iconStart="MagnifyingGlass"
              variant="secondary"
            />
          </div>
          <div class="buttonPreview m-3">
            <h4 class="m-3">Trailing icon:</h4>
            <ButtonV3
              buttonText="Button Small"
              size="small"
              iconEnd="MagnifyingGlass"
              variant="secondary"
            />
            <ButtonV3
              buttonText="Button Medium"
              size="medium"
              iconEnd="MagnifyingGlass"
              variant="secondary"
            />
            <ButtonV3
              buttonText="Button Large"
              size="large"
              iconEnd="MagnifyingGlass"
              variant="secondary"
            />
          </div>
          <div class="buttonPreview m-3">
            <h4 class="m-3">Icon only:</h4>
            <ButtonV3
              size="small"
              iconStart="MagnifyingGlass"
              variant="secondary"
            />
            <ButtonV3
              size="medium"
              iconStart="MagnifyingGlass"
              variant="secondary"
            />
            <ButtonV3
              size="large"
              iconStart="MagnifyingGlass"
              variant="secondary"
            />
          </div>
          <div class="buttonPreview m-3">
            <h4 class="m-3">Disabled:</h4>
            <ButtonV3
              buttonText="Button Small"
              size="small"
              disabled
              variant="secondary"
            />
            <ButtonV3
              buttonText="Button Medium"
              size="medium"
              disabled
              variant="secondary"
            />
            <ButtonV3
              buttonText="Button Large"
              size="large"
              disabled
              variant="secondary"
            />
          </div>
          <hr />
          <h3 class="m-4">Tertiary button:</h3>
          <div class="buttonPreview m-3">
            <h4 class="m-3">Active:</h4>
            <ButtonV3 buttonText="Button Small" size="small" variant="tertiary" />
            <ButtonV3
              buttonText="Button Medium"
              size="medium"
              variant="tertiary"
            />
            <ButtonV3 buttonText="Button Large" size="large" variant="tertiary" />
          </div>
          <div class="buttonPreview m-3">
            <h4 class="m-3">Leading icon:</h4>
            <ButtonV3
              buttonText="Button Small"
              size="small"
              iconStart="MagnifyingGlass"
              variant="tertiary"
            />
            <ButtonV3
              buttonText="Button Medium"
              size="medium"
              iconStart="MagnifyingGlass"
              variant="tertiary"
            />
            <ButtonV3
              buttonText="Button Large"
              size="large"
              iconStart="MagnifyingGlass"
              variant="tertiary"
            />
          </div>
          <div class="buttonPreview m-3">
            <h4 class="m-3">Trailing icon:</h4>
            <ButtonV3
              buttonText="Button Small"
              size="small"
              iconEnd="MagnifyingGlass"
              variant="tertiary"
            />
            <ButtonV3
              buttonText="Button Medium"
              size="medium"
              iconEnd="MagnifyingGlass"
              variant="tertiary"
            />
            <ButtonV3
              buttonText="Button Large"
              size="large"
              iconEnd="MagnifyingGlass"
              variant="tertiary"
            />
          </div>
          <div class="buttonPreview m-3">
            <h4 class="m-3">Icon only:</h4>
            <ButtonV3
              size="small"
              iconStart="MagnifyingGlass"
              variant="tertiary"
            />
            <ButtonV3
              size="medium"
              iconStart="MagnifyingGlass"
              variant="tertiary"
            />
            <ButtonV3
              size="large"
              iconStart="MagnifyingGlass"
              variant="tertiary"
            />
          </div>
          <div class="buttonPreview m-3">
            <h4 class="m-3">Disabled:</h4>
            <ButtonV3
              buttonText="Button Small"
              size="small"
              disabled
              variant="tertiary"
            />
            <ButtonV3
              buttonText="Button Medium"
              size="medium"
              disabled
              variant="tertiary"
            />
            <ButtonV3
              buttonText="Button Large"
              size="large"
              disabled
              variant="tertiary"
            />
          </div>
          <hr />
          <div class="buttonPreview m-3">
            <h4 class="m-3">Cross out button:</h4>
            <CrossOutButton />
          </div>
          <hr />
          <div class="buttonPreview m-3">
            <h4 class="m-3">More button:</h4>
            <MoreButton />
          </div>
          <hr />
          <div class="buttonPreview m-3">
            <h4 class="m-3">Open Close button:</h4>
            <CloseOpenButtonV3 :expanded="true"/>
          </div>
          <hr />
          <div class="buttonPreview m-3">
            <h4 class="m-3">Open Close button:</h4>
            <CloseOpenButtonV3 :expanded="false"/>
          </div>
          <hr />
          <h3 class="m-4">Text button small</h3>
          <div class="buttonPreview m-3">
            <h4 class="m-3">Active:</h4>
            <TextButtonSmall buttonText="löschen" />
          </div>
          <div class="buttonPreview m-3">
            <h4 class="m-3">Leading icon:</h4>
            <TextButtonSmall buttonText="löschen" iconStart="DeleteBlue" />
          </div>
          <div class="buttonPreview m-3">
            <h4 class="m-3">Trailing icon:</h4>
            <TextButtonSmall buttonText="löschen" iconEnd="DeleteBlue" />
          </div>
          <div class="buttonPreview m-3">
            <h4 class="m-3">Icon only:</h4>
            <TextButtonSmall iconStart="DeleteBlue" />
          </div>
          <div class="buttonPreview m-3">
            <h4 class="m-3">Disabled:</h4>
            <TextButtonSmall
              buttonText="löschen"
              iconStart="DeleteBlue"
              disabled
            />
          </div>
          <hr />
          <h3 class="m-4">Text button large</h3>
          <div class="buttonPreview m-3">
            <h4 class="m-3">Active:</h4>
            <TextButtonLarge buttonText="löschen" />
          </div>
          <div class="buttonPreview m-3">
            <h4 class="m-3">Leading icon:</h4>
            <TextButtonLarge buttonText="löschen" iconStart="DeleteBlue" />
          </div>
          <div class="buttonPreview m-3">
            <h4 class="m-3">Trailing icon:</h4>
            <TextButtonLarge buttonText="löschen" iconEnd="DeleteBlue" />
          </div>
          <div class="buttonPreview m-3">
            <h4 class="m-3">Icon only:</h4>
            <TextButtonLarge iconStart="DeleteBlue" />
          </div>
          <div class="buttonPreview m-3">
            <h4 class="m-3">Disabled:</h4>
            <TextButtonLarge
              buttonText="löschen"
              iconStart="DeleteBlue"
              disabled
            />
          </div>
          <hr />
          <h3 class="m-4">Switch:</h3>
          <div class="buttonPreview m-3">
            <h4 class="m-3">Without icon default on:</h4>
            <SwitchV3 :hasIcon="false" :defaultChecked="true" :disabled="false" />
          </div>
          <div class="buttonPreview m-3">
            <h4 class="m-3">With icon default on:</h4>
            <SwitchV3 :hasIcon="true" :defaultChecked="true" :disabled="false" />
          </div>
          <div class="buttonPreview m-3">
            <h4 class="m-3">Without icon default off:</h4>
            <SwitchV3
              :hasIcon="false"
              :defaultChecked="false"
              :disabled="false"
            />
          </div>
          <div class="buttonPreview m-3">
            <h4 class="m-3">With icon default off:</h4>
            <SwitchV3 :hasIcon="true" :defaultChecked="false" :disabled="false" />
          </div>
          <div class="buttonPreview m-3">
            <h4 class="m-3">Disabled without icon, on:</h4>
            <SwitchV3 :hasIcon="false" :defaultChecked="true" :disabled="true" />
          </div>
          <div class="buttonPreview m-3">
            <h4 class="m-3">Disabled with icon, on:</h4>
            <SwitchV3 :hasIcon="true" :defaultChecked="true" :disabled="true" />
          </div>
          <div class="buttonPreview m-3">
            <h4 class="m-3">Disabled with icon, off:</h4>
            <SwitchV3 :hasIcon="true" :defaultChecked="false" :disabled="true" />
          </div>
          <div class="buttonPreview m-3">
            <h4 class="m-3">Disabled without icon, off:</h4>
            <SwitchV3 :hasIcon="false" :defaultChecked="false" :disabled="true" />
          </div>
        </div>
        <hr />
        <div class="buttonPreview m-3">
          <h4 class="m-3">Logo:</h4>
          <LogoV3 />
        </div>
        <hr />
        <h3 class="m-4">Tag:</h3>
        <div class="buttonPreview m-3">
          <h4 class="m-3">Published (for table):</h4>
          <StateTag label="Veröffentlicht" state="published" />
        </div>
        <div class="buttonPreview m-3">
          <h4 class="m-3">Draft (for table):</h4>
          <StateTag label="Entwurf" state="draft" />
        </div>
        <div class="buttonPreview m-3">
          <h4 class="m-3">Error (for table):</h4>
          <StateTag label="Fehler" state="error" />
        </div>
        <div class="buttonPreview m-3">
          <h4 class="m-3">Published (for page):</h4>
          <StateTag label="Veröffentlicht" state="published" size="page" />
        </div>
        <div class="buttonPreview m-3">
          <h4 class="m-3">Draft (for page):</h4>
          <StateTag label="Entwurf" state="draft" size="page" />
        </div>
        <div class="buttonPreview m-3">
          <h4 class="m-3">Error (for page):</h4>
          <StateTag label="Fehler" state="error" size="page" />
        </div>
        <div class="buttonPreview m-3">
          <h4 class="m-3">HVD:</h4>
          <StateTag label="HVD" state="hvd" size="page" />
        </div>
        <div class="buttonPreview m-3">
          <h4 class="m-3">Geopolitical:</h4>
          <StateTag label="GEMEINDEVERBÄNDE" state="geopolitical" size="page" />
        </div>
        <!-- Review Area End -->


      </main>
    </div>
  </template>
  <script>
  import ServiceDPIHome from "./ServiceDPIHome.vue";
  import FAQDPIHome from "./FAQDPIHome.vue";
  import HomeTable from "./HomeTable.vue";
  import ConsultingDPI from "./ConsultingDPI.vue";
  import CommunityDPI from "./CommunityDPI.vue";
  import BlogDPIHome from "./BlogDPIHome.vue";
  import Chip from "./ui/Chip.vue";
  import ButtonV3 from "./ui/ButtonV3.vue";
  import InputV3 from "./ui/InputV3.vue";
  import Dropdown from "./ui/Dropdown.vue";
  import CheckboxV3 from "./ui/CheckboxV3.vue";
  import CrossOutButton from "./ui/CrossOutButton.vue";
  import MoreButton from "./ui/MoreButton.vue";
  import TextButtonSmall from "./ui/TextButtonSmall.vue";
  import SwitchV3 from "./ui/SwitchV3.vue";
  import LogoV3 from "./ui/LogoV3.vue";
  import StateTag from "./ui/StateTag.vue";
  import Toast from "./ui/Toast.vue";
  import TextButtonLarge from "./ui/TextButtonLarge.vue";
  import $ from "jquery";
  import InputField from "./ui/InputField.vue";

  import TextAreaV3 from "./ui/TextAreaV3.vue";
  import DatasetStatus from "./ui/DatasetStatus.vue";
  import Illustration from "./ui/Illustration.vue";
  import CloseOpenButtonV3 from "./ui/CloseOpenButtonV3.vue";

  export default {
    name: "DPI-Home",
    components: {
      HomeTable,
      FAQDPIHome,
      ServiceDPIHome,
      ConsultingDPI,
      CommunityDPI,
      BlogDPIHome,
      Chip,
      ButtonV3,
      InputV3,
      Dropdown,
      CheckboxV3,
      CrossOutButton,
      MoreButton,
      TextButtonSmall,
      SwitchV3,
      LogoV3,
      Toast,
      StateTag,
      TextButtonLarge,
      InputField,
      CloseOpenButtonV3,
      TextAreaV3,
      DatasetStatus,
      Illustration,
    },
    mounted() {
      // Initialisiere alle Tooltips mit der Klasse 'tooltipToggle'
      $(".tooltipToggle").tooltip();
    },
    beforeUnmount() {
      // Zerstöre die Tooltips, um Speicherlecks zu vermeiden
      $(".tooltipToggle").tooltip("dispose");
    },
    methods: {
      onButtonClick() {
        console.log("ButtonV3 clicked!");
      },
    },
  };
  </script>
  <style>
  .buttonPreview {
    display: flex;
    align-items: center;
    gap: 50px;
    margin-bottom: 16px;

    h4 {
      width: 200px;
    }
  }

  .preview {
    display: flex;
    align-items: center;

    h4 {
      width: 150px;
    }

    button {
      margin: 0 0.5rem;
    }
  }

  header {
    justify-content: space-between;
    border: 1px solid lightgray;
    padding: 0 2rem;
  }

  main {
    padding: 0 2rem;

    margin: 5rem 0;
  }

  .table {
    background-color: #f5f5f5 !important;
    padding: 1rem;
    margin: 3rem 0;
  }



  .grayBGDPI {
    background-color: #f5f5f5;
  }
  </style>
