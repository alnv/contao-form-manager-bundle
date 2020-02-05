Vue.component( 'text-field', {
    data: function () {
        return {
            timeout: null
        }
    },
    watch: {
        value: function() {
            if ( this.timeout !== null ) {
                clearTimeout( this.timeout );
            }
            this.timeout = setTimeout(function () {
                this.$emit( 'input', this.value );
            }.bind(this), 400);
        }
    },
    methods: {
        setCssClass: function() {
            let objCssClass = {};
            if ( this.eval['tl_class'] ) {
                objCssClass[this.eval['tl_class']] = true;
            }
            objCssClass['mandatory'] = !!this.eval['mandatory'];
            objCssClass['multiple'] = !!this.eval['multiple'];
            return objCssClass;
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
            default: null,
            type: String|Array
        },
        idPrefix: {
            default: '',
            type: String ,
            required: false
        }
    },
    template:
    '<div class="field-component text" v-bind:class="setCssClass()">' +
        '<div class="field-component-container">' +
            '<label class="label" :for="idPrefix + \'id_\' + name">{{ eval.label }}</label>' +
            '<input type="text" v-model="value" :id="idPrefix + \'id_\' + name" :placeholder="eval.placeholder" v-if="!eval.multiple">' +
            '<div v-if="eval.multiple" class="field-multiple">' +
                '<input v-for="n in eval.size" type="text" v-model="value[n-1]" :id="idPrefix + \'id_\' + name + (n === 1 ? \'\' : n )" :placeholder="eval.placeholder">' +
            '</div>' +
            '<template v-if="!eval.validate"><p class="error" v-for="message in eval.messages">{{ message }}</p></template>' +
            '<template v-if="eval.description"><p class="description">{{ eval.description }}</p></template>' +
        '</div>' +
    '</div>'
});
