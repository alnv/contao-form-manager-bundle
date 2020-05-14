const multiFormSummaryComponent = Vue.component( 'multi-form-summary', {
    data: function () {
        return {
            summaries: [],
            success: true,
            messages: []
        }
    },
    methods: {
        save: function() {
            var objMultiFormSummary = this;
            if ( this.$parent.completeForm.hasOwnProperty('source') ) {
               for ( var i = 0; i < this.$children.length; i++ ) {
                   if ( this.$children[i].$vnode.componentOptions.tag === 'single-form' ) {
                       this.$children[i].getSubmitPromise().then( function ( objResponse ) {
                           if ( objResponse.body ) {
                               this.setModel( objResponse.body.form );
                               this.setPalette( objResponse.body.form );
                               this.saveInstance();
                               if ( objResponse.body.success ) {
                                   objMultiFormSummary.completeMultiForm();
                               }
                           }
                       });
                       break;
                   }
               }
            } else {
                objMultiFormSummary.completeMultiForm();
            }
        },
        completeMultiForm: function() {
            this.$http.post( '/form-manager/save/multiform', {
                forms: objInstances
            },{
                emulateJSON: true,
                'Content-Type': 'application/x-www-form-urlencoded'
            }).then( function ( objResponse ) {
                if ( objResponse.body ) {
                    if (objResponse.body['success']) {
                        this.$parent.afterSubmit(objResponse.body);
                    }
                    else {
                        this.success = false;
                        this.messages = objResponse.body['messages'];
                    }
                }
            });
        }
    },
    mounted: function () {
        for ( var i = 0; i < this.$parent.forms.length; i++ ) {
            if ( this.$parent.forms[i]['component'] === 'single-form' ) {
                var objSummary = {
                    label: this.$parent.forms[i]['label'],
                    form: this.$parent.forms[i],
                    palettes: []
                };
                var objInstance = objInstances[ this.$parent.forms[i]['id'] ];
                if ( objInstance ) {
                    for ( var j = 0; j < objInstance.palettes.length; j++ ) {
                        objSummary.palettes.push({
                            label: objInstance.palettes[j]['label'],
                            fields: []
                        });
                        for ( var c = 0; c < objInstance.palettes[j].fields.length; c++ ) {
                            if ( objInstance.palettes[j].fields[c].name ) {
                                objSummary.palettes[j].fields.push({
                                    label: objInstance.palettes[j].fields[c]['label'],
                                    value: objInstance.palettes[j].fields[c]['labelValue']
                                });
                            }
                        }
                    }
                }
                this.summaries.push(objSummary);
            }
        }
    },
    template:
    '<div class="summary-component">' +
        '<div class="summary-component-container">' +
            '<slot :summaries="summaries">' +
                '<div v-for="(summary,index) in summaries" class="palette">' +
                    '<p class="palette-name" v-html="summary.label"></p>' +
                    '<template v-for="palette in summary.palettes">' +
                        '<div class="palette-fields" v-for="field in palette.fields">' +
                            '<p class="field-name" v-html="field.label"></p>' +
                            '<p class="field-value" v-if="!Array.isArray( field.value )" v-html="field.value"></p>' +
                            '<p class="field-value" v-if="Array.isArray( field.value )"><ul><li v-for="value in field.value" v-html="value"></li></ul></p>' +
                        '</div>' +
                    '</template>' +
                    '<button class="summary-button" @click="$parent.goTo(summary.form,index)">Ändern</button>' +
                '</div>' +
            '</slot>' +
            '<template v-if="$parent.completeForm.hasOwnProperty(\'source\')">' +
                '<component is="single-form" :validate-only="true" :disable-submit="true" :id="$parent.completeForm.id" :source="$parent.completeForm.source" :identifier="$parent.completeForm.identifier"></component>' +
            '</template>' +
            '<div v-if="!success" class="messages error">' +
                '<ul>' +
                    '<li v-for="message in messages" class="error">{{ message }}</li>' +
                '</ul>' +
            '</div>' +
            '<button @click="save" class="submit">Kostenpflichtig bestellen</button>' +
        '</div>' +
    '</div>'
});
const multiFormComponent = Vue.component( 'multi-form', {
    data: function () {
        return {
            active: {}
        }
    },
    methods: {
        goTo: function (form, index) {
            if ( form.valid ) {
                this.setActive( form, index );
            }
        },
        setActive: function (form, index) {
            for ( var i = 0; i < this.$children.length; i++ ) {
                if ( typeof this.$children[i].saveInstance !== 'undefined' ) {
                    this.$children[i].saveInstance();
                }
            }
            this.active = form;
            this.active.index = index;
            this.$parent.setLoadingAlert('', this);
        },
        afterSubmit: function(objResponse) {
            var strRedirect = this.successRedirect;
            if (typeof objResponse.redirect !== 'undefined' && objResponse.redirect) {
                strRedirect = objResponse.redirect;
            }
            if (strRedirect) {
                window.location.href = strRedirect;
            }
        },
        setComplete: function () {
            //
        }
    },
    mounted: function () {
        for ( var i = 0; i < this.forms.length; i++ ) {
            this.forms[i].component = 'single-form';
            this.forms[i].valid = false;
            if (!i) {
                this.active = this.forms[i];
                this.active.valid = true;
                this.active.index = i;
            }
        }
        this.forms.push({
            component: 'multi-form-summary',
            label: 'Zusammenfassung',
            valid: false
        });
    },
    props: {
        forms: {
            default: [],
            type: Array,
            required: true
        },
        completeForm: {
            default: {},
            type: Object,
            required: false
        },
        successRedirect: {
            default: '',
            type: String,
            required: false
        },
    },
    template:
    '<div class="forms-component">' +
        '<div class="forms-component-container">' +
            '<div class="forms-navigation">' +
                '<div class="forms-navigation-container">' +
                    '<nav>' +
                        '<ul>' +
                            '<li v-for="(form, index) in forms" v-bind:class="{active: form.valid}">' +
                                '<strong v-if="form.index === active.index" v-html="form.label"></strong>' +
                                '<span v-else v-html="form.label"></span>' +
                            '</li>' +
                        '</ul>' +
                    '</nav>' +
                '</div>' +
            '</div>' +
            '<template>' +
                '<component :is="active.component" :validate-only="true" :id="active.id" :source="active.source" :identifier="active.identifier" submit-label="Weiter">' +
                    '<template v-if="active.component === \'multi-form-summary\'" v-slot:default="slotProps">' +
                        '<slot :summaries="slotProps.summaries" :goTo="goTo"></slot>' +
                    '</template>' +
                '</component>' +
            '</template>' +
        '</div>' +
    '</div>'
});
