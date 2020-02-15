Vue.component( 'form-wizard', {
    data: function () {
        return {
            fields: [],
            editMode: false,
            selectedValue: {}
        }
    },
    methods: {
        fetch: function () {
            this.$http.get( '/form-manager/getFormWizard/' + this.getIdentifier(), {
                params: {
                    wizard: this.name
                }
            }).then(function ( objResponse ) {
                if ( objResponse.body ) {
                    if ( !objResponse.body.length ) {
                        return null;
                    }
                    this.fields = objResponse.body[0].fields;
                    this.setValues();
                }
            });
        },
        getIdentifier: function() {
            if ( this.$parent.identifier ) {
                return this.$parent.identifier;
            }
            return this.eval.identifier;
        },
        addValue: function(blnEmpty) {
            var objValue = {};
            for ( var i = 0; i < this.fields.length; i++ ) {
                var objField = this.fields[i];
                objValue[ objField.name ] = objField.value;
                if ( objField.value ) {
                    blnEmpty = false;
                }
            }
            if ( blnEmpty ) {
                return null;
            }
            if (!this.eval['maxEntities']) {
                this.value.push(objValue);
            }
            if ( this.eval['maxEntities'] > 0 && this.value.length < this.eval['maxEntities'] ) {
                this.value.push(objValue);
            }
            this.editValue(objValue);
        },
        editValue: function(value) {
            this.editMode = true;
            this.selectedValue = value;
        },
        deleteValue: function(value) {
            this.editMode = false;
            this.selectedValue = {};
            for ( var i = 0; i < this.value.length; i++ ) {
                if ( this.value[i] === value ) {
                    this.value.splice(i, 1);
                }
            }
        },
        setValues: function() {
            if ( typeof this.eval.values !== 'undefined' ) {
                this.value = this.eval.values;
            }
            if ( typeof this.value === 'undefined' || this.value === null) {
                this.value = [];
            }
            if (!this.eval['useValuesAsDefault']) {
                this.addValue(true);
            }
            if ( Array.isArray(this.value) && !this.value.length && this.eval['showFormIsEmpty'] ) {
                this.addValue(false);
            }
        },
        setFieldCssClass: function(field,value) {
            var objCssClass = {};
            objCssClass[field.name] = true;
            objCssClass['empty-value'] = !value[field.name];
            if ( this.eval['tl_class'] ) {
                objCssClass[this.eval['tl_class']] = true;
            }

            return objCssClass;
        },
        setCssClass: function() {
            var objCssClass = {};
            if ( this.eval['tl_class'] ) {
                objCssClass[this.eval['tl_class']] = true;
            }
            if ( this.eval['mandatory'] ) {
                objCssClass['mandatory'] = true;
            }
            return objCssClass;
        },
        getLabel: function(value,field) {
            if ( field.options && field.options.length ) {
                for ( var i = 0; i < field.options.length; i++ ) {
                    var objOption = field.options[i];
                    if ( objOption['value'] === value ) {
                        return objOption['label'];
                    }
                }
            }
            if ( typeof value === 'object' && JSON.stringify( value ) === '{}' || JSON.stringify( value ) === '[]' ) {
                return  '';
            }
            return value;
        },
        getStringifyValue: function() {
            return JSON.stringify(this.value);
        }
    },
    watch: {
        value: {
            handler: function () {
                this.$emit('input',this.value);
            },
            deep: true
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
            type: Array,
            default: [],
            required: false
        },
        editButtonLabel: {
            default: 'Ändern',
            required: false,
            type: String
        },
        closeButtonLabel: {
            default: 'Schließen',
            required: false,
            type: String
        },
        addButtonLabel: {
            default: 'Hinzufügen',
            required: false,
            type: String
        },
        deleteButtonLabel: {
            default: 'Entfernen',
            required: false,
            type: String
        }
    },
    mounted: function () {
        this.fetch();
    },
    template:
        '<div class="field-component form-wizard" v-bind:class="setCssClass()">' +
            '<div class="field-component-container">' +
                '<input type="hidden" :value="getStringifyValue()" :name="name">' +
                '<p v-if="eval.label" class="label">{{ eval.label }}</p>' +
                '<div v-if="value && value.length" class="entities">' +
                    '<div v-for="val in value" class="entity" v-bind:class="{\'active\': val === selectedValue}">' +
                        '<div class="rows">' +
                            '<template v-for="field in fields">' +
                                '<div class="row" v-bind:class="setFieldCssClass(field,val)"><span class="name">{{ field.label }}: </span><span class="value">{{ getLabel(val[field.name],field) }}</span></div>'+
                            '</template>' +
                        '</div>'+
                        '<div class="operations">' +
                            '<button type="button" v-on:click.prevent="editValue(val)" class="button edit">{{ editButtonLabel }}</button>' +
                            '<button v-if="eval.allowToDelete" type="button" v-on:click.prevent="deleteValue(val)" class="button delete">{{ deleteButtonLabel }}</button>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="forms" v-if="editMode">' +
                    '<div class="form" v-for="(val,index) in value" v-if="val && val === selectedValue">' +
                        '<template v-for="field in fields"  v-if="field.component">' +
                            '<component :is="field.component" :eval="field" :name="field.name" :id-prefix="name" v-model="val[field.name]"></component>' +
                        '</template>' +
                    '</div>' +
                '</div>' +
                '<div class="operations" v-if="value && value.length < eval.maxEntities || !eval.maxEntities">' +
                    '<button type="button" v-on:click.prevent="addValue(false)" class="button add">{{ addButtonLabel }}</button>' +
                '</div>' +
                '<template v-if="!eval.validate"><p class="error" v-for="message in eval.messages">{{ message }}</p></template>' +
                '<template v-if="eval.description"><p class="description">{{ eval.description }}</p></template>' +
            '</div>' +
        '</div>'
});
