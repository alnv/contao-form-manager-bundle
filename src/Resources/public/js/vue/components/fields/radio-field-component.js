Vue.component( 'radio-field', {
    data: function () {
        return {
            //
        }
    },
    methods: {
        checked: function ( strValue ) {
            if ( Array.isArray( this.value ) ) {
                return this.value.indexOf( strValue ) !== -1;
            }
            return this.value == strValue;
        },
        setDefault: function () {
            if ( this.eval.options ) {
                for ( var i = 0; i < this.eval.options.length; i++ ) {
                    if ( this.eval.options[i]['default'] ) {
                        this.value = this.eval.options[i]['value'];
                    }
                }
            }
        },
        setCssClass: function() {
            let objCssClass = {};
            if ( this.eval['tl_class'] ) {
                objCssClass[this.eval['tl_class']] = true;
            }
            if ( this.eval['mandatory'] ) {
                objCssClass['mandatory'] = true;
            }
            objCssClass[this.name] = true;
            return objCssClass;
        }
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
        },
        idPrefix: {
            default: '',
            type: String ,
            required: false
        },
        noLabel: {
            type: Boolean,
            default: false,
            required: false
        }
    },
    mounted: function () {
        this.setDefault();
    },
    template:
    '<div class="field-component radio" v-bind:class="setCssClass()">' +
        '<div class="field-component-container">' +
            '<p v-if="eval.label && !noLabel" class="label" v-html="eval.label"></p>' +
            '<span v-for="(option, index) in eval.options" class="radio-container" v-bind:class="{ \'checked\': checked( option.value ) }">' +
                '<input type="radio" v-model="value" :value="option.value" :id="idPrefix + \'id_\' + name + \'_\' + index">' +
                '<slot name="label" v-bind:label="option.label" v-bind:id="idPrefix + \'id_\' + name + \'_\' + index"><label :for="idPrefix + \'id_\' + name + \'_\' + index" v-html="option.label"></label></slot>' +
            '</span>' +
            '<template v-if="!eval.validate"><p class="error" v-for="message in eval.messages">{{ message }}</p></template>' +
            '<div v-if="eval.description" v-html="eval.description"></div>' +
        '</div>' +
    '</div>'
});
