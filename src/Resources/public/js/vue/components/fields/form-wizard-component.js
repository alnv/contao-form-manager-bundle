Vue.component( 'form-wizard', {
    data: function () {
        return {
            fields: [],
            values: [],
            editMode: false,
            selectedValue: {},
            hasDefaultValues: false
        }
    },
    methods: {
        fetch: function () {
            this.values = []; // improve
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
            let objValue = {};
            for ( let i = 0; i < this.fields.length; i++ ) {
                let objField = this.fields[i];
                objValue[ objField.name ] = objField.value;
                if ( objField.value ) {
                    blnEmpty = false;
                }
            }
            if ( blnEmpty ) {
                return null;
            }
            if (!this.eval['maxEntities']) {
                this.values.push(objValue);
            }
            if ( this.eval['maxEntities'] > 0 && this.values.length < this.eval['maxEntities'] ) {
                this.values.push(objValue);
            }
            if ( !this.hasDefaultValues ) {
                this.editValue(objValue);
            }
        },
        editValue: function(value) {
            this.editMode = true;
            this.selectedValue = value;
        },
        deleteValue: function(value) {
            this.editMode = false;
            this.selectedValue = {};
            for ( let i = 0; i < this.values.length; i++ ) {
                if ( this.values[i] === value ) {
                    this.values.splice(i, 1);
                }
            }
        },
        setValues: function() {
            if ( typeof this.eval.values !== 'undefined' ) {
                if ( Array.isArray( this.eval.values ) ) {
                    this.hasDefaultValues = !!this.eval.values.length;
                    for ( let i = 0; i < this.eval.values.length; i++ ) {
                        let objValue = this.eval.values[i];
                        for ( let j = 0; j < this.fields.length; j++ ) {
                            if ( objValue.hasOwnProperty( this.fields[j]['name'] ) ) {
                                this.fields[j]['value'] = objValue[ this.fields[j]['name'] ];
                            }
                        }
                        if ( !this.eval['useValuesAsDefault'] ) {
                            this.addValue(true);
                        }
                    }
                    if ( Array.isArray( this.eval.values ) && !this.eval.values.length && this.eval['showFormIsEmpty'] ) {
                        this.addValue(false);
                    }
                }
            }
        },
        setFieldCssClass: function(field,value) {
            let objCssClass = {};
            objCssClass[field.name] = true;
            objCssClass['empty-value'] = !value[field.name];
            if ( this.eval['tl_class'] ) {
                objCssClass[this.eval['tl_class']] = true;
            }

            return objCssClass;
        },
        setCssClass: function() {
            let objCssClass = {};
            if ( this.eval['tl_class'] ) {
                objCssClass[this.eval['tl_class']] = true;
            }
            if ( this.eval['mandatory'] ) {
                objCssClass['mandatory'] = true;
            }
            return objCssClass;
        },
        getLabel: function(value,field) {
            if ( field.options ) {
                for ( let i = 0; i < field.options.length; i++ ) {
                    let objOption = field.options[i];
                    if ( objOption['value'] === value ) {
                        return  objOption['label'];
                    }
                }
            }
            if ( typeof value === 'object' && JSON.stringify( value ) === '{}' || JSON.stringify( value ) === '[]' ) {
                return  '';
            }
            return value;
        },
        setInput: function (field) {
            //
        }
    },
    watch: {
        values: {
            handler: function () {
                this.$emit( 'input', this.values );
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
        values: {
            default: [],
            type: Array,
            required: false
        },
        editButtonLabel: {
            default: 'Ändern',
            type: String,
            required: false
        },
        closeButtonLabel: {
            default: 'Schließen',
            type: String,
            required: false
        },
        addButtonLabel: {
            default: 'Hinzufügen',
            type: String,
            required: false
        },
        deleteButtonLabel: {
            default: 'Entfernen',
            type: String,
            required: false
        }
    },
    mounted: function () {
        this.fetch();
    },
    template:
        '<div class="field-component form-wizard" v-bind:class="setCssClass()">' +
            '<div class="field-component-container">' +
                '<p v-if="eval.label" class="label">{{ eval.label }}</p>' +
                '<div v-if="values && values.length" class="entities">' +
                    '<div class="entity" v-for="value in values">' +
                        '<div class="rows">' +
                            '<template v-for="field in fields">' +
                                '<div class="row" v-bind:class="setFieldCssClass(field,value)"><span class="name">{{ field.label }}: </span><span class="value">{{ getLabel( value[ field.name ], field ) }}</span></div>'+
                            '</template>' +
                        '</div>'+
                        '<div class="operations">' +
                            '<button type="button" v-on:click.prevent="editValue(value)" class="button edit">{{ editButtonLabel }}</button>' +
                            '<button v-if="eval.allowToDelete" type="button" v-on:click.prevent="deleteValue(value)" class="button delete">{{ deleteButtonLabel }}</button>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="forms" v-if="editMode">' +
                    '<div class="form" v-for="(value,index) in values" v-if="values && value === selectedValue">' +
                        '<template v-for="field in fields"  v-if="field.component">' +
                            '<component :is="field.component" :eval="field" :name="field.name" :id-prefix="name" v-model="value[field.name]" v-on:input="setInput(field)"></component>' +
                        '</template>' +
                    '</div>' +
                '</div>' +
                '<div class="operations" v-if="values && values.length < eval.maxEntities || !eval.maxEntities">' +
                    '<button type="button" v-on:click.prevent="addValue(false)" class="button add">{{ addButtonLabel }}</button>' +
                '</div>' +
                '<template v-if="!eval.validate"><p class="error" v-for="message in eval.messages">{{ message }}</p></template>' +
                '<template v-if="eval.description"><p class="description">{{ eval.description }}</p></template>' +
            '</div>' +
        '</div>'
});
