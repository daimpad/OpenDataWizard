<template>
    <div class="formkitProperty" :class="[context.attrs.identifier]" v-for="key in items" :key="key">
      <h4>{{ context.label }}</h4>
      
      <div class="horizontal-wrapper">
        
        <div class="formkitCmpWrap w-100">
        
          <div class="formkitWrapRepeatable">
            <slot>
              <FormKit outer-class="w-100" placeholder="Please provide a number"/>
            </slot>
          </div>
  
        </div>
  
      </div>
      <!-- <div class="interactionWrapper">
        <div class="formkit-remover ball" @click="removeItem" :data-key="key">
          <span>x</span>
        </div>
        <div @click="addItem" class="ball formkit-adder"><span>+</span></div>
      </div> -->
    </div>
    
  </template>
  
  <script>
  import { token } from "@formkit/utils";
  
  export default {
    props: {
      name: String,
      children: Array,
      context: Object,
      
    },
    data() {
      return {
        values: [],
        items: [this.newId()],
        camel2title: (str) =>
          str
            .replace(/([A-Z])/g, (match) => ` ${match}`)
            .replace(/^./, (match) => match.toUpperCase())
            .trim(),
      }
    },
    methods: {
      newId() {
        return `${this.name}_${token()}`;
      },
      addItem() {
        this.items.push(this.newId());
      },
      removeItem(e) {
        const key = e.target.getAttribute('data-key');
        const index = this.items.indexOf(key);
        if (index >= 0) {
          this.items.splice(index, 1);
        }
      }
    }
  }
  </script>
  
  
  
  <style lang="scss">
  .horizontal-wrapper {
    display: flex;
    flex-direction: row;
    width: 100%;
  }
  
  .interactionWrapper {
    width: 10%;
    display: flex;
    align-items: center;
    position: relative;
  
  }
  
  .ball {
    position: absolute;
    display: flex;
    text-align: center;
    flex-direction: column;
    justify-content: center;
    height: 1.5rem;
    width: 1.5rem;
    font-size: 1em;
    font-weight: bold;
    cursor: pointer;
    border-radius: 50%;
    margin: 5px;
    transition: all ease-in-out 200ms;
  }
  
  .formkit-remover {
    left: 30px;
    top: 22px;
    background-color: red;
    color: white;
  
  
    span {
      pointer-events: none;
      position: relative;
      bottom: 0.1rem;
    }
  
    &:hover {
      background-color: darkred;
  
    }
  }
  
  .formkit-adder {
    top: 22px;
    background-color: darkseagreen;
    color: white;
  
    span {
      position: relative;
      bottom: 0.13rem;
    }
  
    &:hover {
      background-color: darkgreen;
  
    }
  }
  
  .dpi button {
    align-self: flex-start;
  }
  </style>
  