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
            return this.value === strValue;
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
        }
    },
    template:
    '<div class="field-component checkbox">' +
        '<div class="field-component-container">' +
            '<p class="label">{{eval.label}}</p>' +
            '<span v-if="eval.multiple" class="all">' +
                '<input type="checkbox" v-model="selectAll" id="selectAll" @click="setSelectAll()">' +
                '<label for="selectAll">Alle</label>' +
            '</span>'+
            '<span v-for="(option,index) in eval.options" v-bind:class="{ \'checked\': checked( option.value ) }">' +
                '<input v-if="eval.multiple" type="checkbox" v-model="value" :value="option.value" :id="name + \'_\' + index">' +
                '<input v-if="!eval.multiple" type="checkbox" v-model="value" true-value="1" false-value="" :id="name + \'_\' + index">' +
                '<label :for="name + \'_\' + index">{{option.label}}</label>' +
            '</span>' +
        '</div>' +
    '</div>'
});
