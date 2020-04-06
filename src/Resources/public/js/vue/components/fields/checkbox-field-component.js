Vue.component( 'checkbox-field', {
    data: function () {
        return {
            selectAll: false
        }
    },
    methods: {
        checked: function ( strValue ) {
            if ( Array.isArray(this.value) ) {
                return this.value.indexOf( strValue ) !== -1;
            }
            return strValue == this.value;
        },
        setSelectAll: function () {
            this.selectAll = !this.selectAll;
            if ( this.selectAll && Array.isArray( this.eval.options ) ) {
                this.value = [];
                for ( var i = 0; i < this.eval.options.length; i++ ) {
                    this.value.push( this.eval.options[i]['value'] );
                }
            }
            if ( !this.selectAll ) {
                this.value = [];
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
            objCssClass['single'] = !this.eval.multiple;
            objCssClass[this.name] = true;
            return objCssClass;
        }
    },
    watch: {
        value: function() {
            this.$emit( 'input', this.value );
            if ( this.eval.submitOnChange ) {
                this.$parent.submitOnChange(this.value, this.name, this.eval['isSelector'])
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
            type: String,
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
        },
        noLabel: {
            type: Boolean,
            default: false,
            required: false
        }
    },
    template:
    '<div class="field-component checkbox" v-bind:class="setCssClass()">' +
        '<div class="field-component-container">' +
            '<p v-if="eval.multiple && !noLabel" class="label" v-html="eval.label"></p>' +
            '<span v-if="eval.multiple" class="all checkbox-container" v-bind:class="{ \'checked\': selectAll }">' +
                '<input type="checkbox" v-model="selectAll" :id="idPrefix + \'selectAll\'" @click="setSelectAll()">' +
                '<label :for="idPrefix + \'selectAll\'">Alle auswählen</label>' +
            '</span>'+
            '<span v-for="(option,index) in eval.options" class="checkbox-container" v-bind:class="{\'checked\': checked(option.value)}">' +
                '<input v-if="eval.multiple" type="checkbox" v-model="value" :value="option.value" :id="idPrefix + \'id_\' + name + \'_\' + index">' +
                '<input v-if="!eval.multiple" type="checkbox" v-model="value" true-value="1" false-value="" :id="idPrefix + \'id_\' + name + \'_\' + index">' +
                '<slot name="label" v-bind:label="option.label" v-bind:id="idPrefix + \'id_\' + name + \'_\' + index"><label :for="idPrefix + \'id_\' + name + \'_\' + index" v-html="option.label"></label></slot>' +
            '</span>' +
            '<template v-if="!eval.validate"><p class="error" v-for="message in eval.messages" v-html="message"></p></template>' +
            '<div v-if="eval.description" v-html="eval.description" class="info"></div>' +
        '</div>' +
    '</div>'
});
