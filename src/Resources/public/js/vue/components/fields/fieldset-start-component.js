Vue.component( 'fieldset-start', {
    props: {
        eval: {
            default: {},
            type: Object,
            required: true
        },
        name: {
            default: '',
            type: Object,
            required: true
        },
        value: {
            type: Object,
            default: null,
            required: false
        }
    },
    template: '<div class="fieldset-component"><div class="fieldset-component-container"><span class="legend">{{eval.label}}</span></div>'
});