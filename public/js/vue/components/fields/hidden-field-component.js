Vue.component( 'hidden-field', {
    data: function () {
        return {}
    },
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
            default: null,
            type: String|Array
        }
    },
    template:
    '<div class="field-component hidden">' +
        '<div class="field-component-container">' +
            '<input type="hidden" v-model="value" :name="name">' +
        '</div>' +
    '</div>'
});

