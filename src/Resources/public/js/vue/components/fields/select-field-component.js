Vue.component( 'select-field', {
    data: function () {
        return {
            value: ''
        }
    },
    methods: {
        //
    },
    watch: {
        value: function() {
            this.$emit( 'input', this.value );
            if ( this.eval.submitOnChange ) {
                this.$parent.submitOnChange( this.value, this.name, this.eval.isSelector )
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
            type: String,
            required: true
        },
        value: {
            default: '',
            required: false,
            type: Object|Array
        }
    },
    mounted: function () {
        //
    },
    template:
    '<div class="field-component select">' +
        '<div class="field-component-container">' +
            '<label>{{ eval.label }}</label>' +
            '<select v-model="value">' +
                '<option v-for="option in eval.options" :value="option.value">{{option.label}}</option>' +
            '</select>' +
        '</div>' +
    '</div>'
});

