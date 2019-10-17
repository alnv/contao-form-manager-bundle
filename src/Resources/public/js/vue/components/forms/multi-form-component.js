const multiFormSummaryComponent = Vue.component( 'multi-form-summary', {
    data: function () {
        return {
            summaries: []
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
                               this.setValidation( objResponse.body );
                               if ( objResponse.body.success ) {
                                   objMultiFormSummary.completeMultiForm();
                               }
                               for ( var x = 0; x < this.$children.length; x++ ) {
                                   this.$children[x].$forceUpdate();
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
            //
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
                                    label: objInstance.palettes[j].fields[c].label,
                                    value: objInstance.model[ objInstance.palettes[j].fields[c].name ]
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
            '<div v-for="(summary,index) in summaries">' +
                '<p class="summary-headline">{{ summary.label }}</p>' +
                '<template v-for="palette in summary.palettes">' +
                    '<div class="summary-fields" v-for="field in palette.fields">' +
                        '<p><span class="label">{{ field.label }}: </span><span class="value">{{ field.value }}</span></p>' +
                    '</div>' +
                '</template>' +
                '<button class="summary-button" @click="$parent.goTo(summary.form,index)">Ändern</button>' +
            '</div>' +
            '<template v-if="$parent.completeForm.hasOwnProperty(\'source\')">' +
                '<component is="single-form" v-bind:disable-submit="true" v-bind:id="$parent.completeForm.id" v-bind:source="$parent.completeForm.source" v-bind:identifier="$parent.completeForm.identifier"></component>' +
            '</template>' +
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
        },
        setComplete: function () {
            //
        }
    },
    mounted: function () {
        for ( var i = 0; i < this.forms.length; i++ ) {
            this.forms[i].component = 'single-form';
            this.forms[i].valid = false;
            if ( !i ) {
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
    template:
    '<div class="forms-component">' +
        '<div class="forms-component-container">' +
            '<div class="forms-navigation">' +
                '<div class="forms-navigation-container">' +
                    '<nav>' +
                        '<ul>' +
                            '<li v-for="(form, index) in forms" v-bind:class="{active: form.valid}">' +
                                '<strong v-if="form.index === active.index">{{ form.label }}</strong>' +
                                '<a v-if="form.index !== active.index" @click="goTo(form, index)">{{ form.label }}</a>' +
                            '</li>' +
                        '</ul>' +
                    '</nav>' +
                '</div>' +
            '</div>' +
            '<template>' +
                '<component :is="active.component" v-bind:id="active.id" v-bind:source="active.source" v-bind:identifier="active.identifier"></component>' +
            '</template>' +
        '</div>' +
    '</div>'
});
