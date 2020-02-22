Vue.component( 'select-field', {
    data: function () {
        return {
            //
        }
    },
    methods: {
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
                this.$parent.submitOnChange( this.value, this.name, this.eval['isSelector'] )
            }
        }
    },
    created: function() {
        if ( this.value !== null ) {
            return null;
        }
        // this.value = this.multiple ? [] : '';
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
            default: {},
            required: false,
            type: String|Array
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
    template:
    '<div class="field-component select" v-bind:class="setCssClass()">' +
        '<div class="field-component-container">' +
            '<label v-if="eval.label && !noLabel" class="label" :for="idPrefix + \'id_\' + name" v-html="eval.label"></label>' +
            '<div class="select-container">' +
                '<v-select v-model="value" :placeholder="eval.label" :multiple="eval.multiple" :options="eval.options" :id="idPrefix + \'id_\' + name" :reduce="value => value.value" label="label" class="tl_select">' +
                    '<template v-slot:selected-option="option">' +
                        '<slot name="label" v-bind:label="option.label" v-html="option.label"></slot>' +
                    '</template>' +
                    '<template v-slot:option="option">' +
                        '<slot name="label" v-bind:label="option.label" v-html="option.label"></slot>' +
                    '</template>' +
                '</v-select>' +
            '</div>' +
            '<template v-if="!eval.validate"><p class="error" v-for="message in eval.messages">{{ message }}</p></template>' +
            '<div v-if="eval.description" v-html="eval.description"></div>' +
        '</div>' +
    '</div>'
});

