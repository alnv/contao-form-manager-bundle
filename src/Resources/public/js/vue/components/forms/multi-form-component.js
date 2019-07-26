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
        }
    },
    methods: {
        goTo: function (form, index) {
            this.setActive(form, index);
        },
        setActive: function (form, index) {
            this.active = form;
            this.active.index = index;
        }
    },
    mounted: function () {
        this.active = this.forms[0];
        this.active.index = 0;
    },
    template:
    '<div class="forms-component">' +
        '<div class="forms-component-container">' +
            '<div class="forms-navigation">' +
                '<div class="forms-navigation-container">' +
                    '<nav>' +
                        '<ul>' +
                            '<li v-for="(form,index) in forms">' +
                                '<strong v-if="form.index === active.index">{{ form.label }}</strong>' +
                                '<a v-if="form.index !== active.index" @click="goTo(form, index)">{{ form.label }}</a>' +
                            '</li>' +
                        '</ul>' +
                    '</nav>' +
                '</div>' +
            '</div>' +
            '<template v-if="active.source && active.identifier">' +
                '<component is="single-form" v-bind:id="active.id" v-bind:source="active.source" v-bind:identifier="active.identifier"></component>' +
            '</template>' +
        '</div>' +
    '</div>'
});
