import { reactive, toRef, ref, watch } from 'vue'
import { getNode, createMessage } from '@formkit/core'

export default function useSteps() {
  const activeStep = ref('')
  const steps = reactive({})
  let subSteps = ref([])
  const visitedSteps = ref([]) // track visited steps

  // Watch our activeStep and store visited steps
  watch(activeStep, (newStep, oldStep) => {
    if (oldStep && !visitedSteps.value.includes(oldStep)) {
      visitedSteps.value.push(oldStep)
    }

    // Trigger showing validation on fields if a group has been visited
    visitedSteps.value.forEach((step) => {
      const node = getNode(step)

      if (node === undefined) return;

      // the node.walk() method walks through all the descendants of the current node
      // and executes the provided function.
      node.walk((n) => {
        n.store.set(
          createMessage({
            key: 'submitted',
            value: true,
            visible: false
          })
        )
      })
    })
  });

  const stepPlugin = (node) => {
    if (node.props.type == "group") {
      // builds an object of the top-level groups
      steps[node.name] = steps[node.name] || {}

      node.on('created', () => {
        // use 'on created' to ensure context object is available
        const state = toRef(node.context.state, 'valid')
        steps[node.name].valid = state;
      })

      // Store or update the count of blocking validation messages.
      // FormKit emits the "count:blocking" event (with the count) each time
      // the count changes.
      node.on('count:blocking', ({ payload: count }) => {
        steps[node.name].blockingCount = count
      })

      // Store or update the count of backend error messages.
      node.on('count:errors', ({ payload: count }) => {
        steps[node.name].errorCount = count
      })

      // set the active tab to the 1st tab
      if (activeStep.value === '') {
        activeStep.value = node.name
      }
     
      subSteps.value.push(node.value) 
      // Stop plugin inheritance to descendant nodes
      return false
    }

  }



  return { visitedSteps, activeStep, steps, subSteps, stepPlugin }
}