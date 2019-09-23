Vue.component( 'radio-field', {
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
            if ( this.eval.submitOnChange ) {
                this.$parent.submitOnChange( this.value, this.name )
            }
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
            type: String,
            default: null
        }
    },
    template:
    '<div class="field-component radio">' +
        '<div class="field-component-container">' +
            '{{eval.label}}' +
            '<input type="radio" v-model="value">' +
        '</div>' +
    '</div>'
});