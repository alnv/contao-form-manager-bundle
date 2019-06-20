Vue.component( 'textarea-field', {
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
    '<div class="field-component textarea">' +
        '<div class="field-component-container">' +
            '{{eval.label}}' +
            '<textarea v-model="value"></textarea>' +
        '</div>' +
    '</div>'
});
