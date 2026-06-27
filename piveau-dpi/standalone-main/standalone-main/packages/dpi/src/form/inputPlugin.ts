import inputDefinitions from "./inputDefinitions";

const createInputPlugin = () => {
    const keys = Object.keys(inputDefinitions);
    const plugin = () => {};
    plugin.library = node => {
        const type = node.props.type;
        keys.some(key => {
           if (type === key) {
               node.define(inputDefinitions[key]);
               return true;
           }
        });
    };
    return plugin;
};

const inputPlugin = createInputPlugin();

export default inputPlugin;

