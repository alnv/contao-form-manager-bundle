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
    mounted: function() {
        this.value = {};
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
        }
    },
    template:
    '<div class="field-component select" v-bind:class="setCssClass()">' +
        '<div class="field-component-container">' +
            '<label v-if="eval.label" class="label" :for="idPrefix + \'id_\' + name">{{ eval.label }}</label>' +
            '<div class="select-container">' +
                '<select v-model="value" :id="idPrefix + \'id_\' + name" :multiple="eval.multiple">' +
                    '<option v-for="option in eval.options" :value="option.value">{{option.label}}</option>' +
                '</select>' +
            '</div>' +
            '<template v-if="!eval.validate"><p class="error" v-for="message in eval.messages">{{ message }}</p></template>' +
            '<template v-if="eval.description"><p class="description">{{ eval.description }}</p></template>' +
        '</div>' +
    '</div>'
});

