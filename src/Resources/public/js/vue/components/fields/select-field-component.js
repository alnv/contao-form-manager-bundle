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
        this.value = this.multiple ? [] : '';
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
            '<div v-if="!eval.multiple" class="select-container">' +
                '<select v-model="value" :id="idPrefix + \'id_\' + name">' +
                    '<option v-for="option in eval.options" :value="option.value">{{option.label}}</option>' +
                '</select>' +
            '</div>' +
            '<div v-if="eval.multiple">' +
                '<v-select v-model="value" :placeholder="eval.label" :options="eval.options" :multiple="true" :id="idPrefix + \'id_\' + name" :reduce="value => value.value" label="label"></v-select>' +
            '</div>' +
            '<template v-if="!eval.validate"><p class="error" v-for="message in eval.messages">{{ message }}</p></template>' +
            '<template v-if="eval.description"><p class="description">{{ eval.description }}</p></template>' +
        '</div>' +
    '</div>'
});

