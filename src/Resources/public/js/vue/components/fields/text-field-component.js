Vue.component( 'text-field', {
    data: function () {
        return {
            //
        }
    },
    methods: {
        //
    },
    watch: {
        value: function() {
            this.$emit( 'input', this.value );
        }
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
            type: Object,
            default: null,
            required: false
        }
    },
    template:
    '<div class="field-component">' +
        '<div class="field-component-container">' +
            '<input type="text" v-model="value">' +
        '</div>' +
    '</div>'
});
