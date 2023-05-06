const tableListComponent = Vue.component( 'table-list', {
    data: function () {
        return {
            list: [],
            fields: [],
            labels: {},
            reload: false
        }
    },
    methods: {
        fetch: function () {
            this.reload = true;
            this.$http.post('/form-manager/list-view',
                {
                    module: this.module,
                    order: this.order
                },
                {
                emulateJSON: true,
                'Content-Type': 'application/x-www-form-urlencoded'
                }
            ).then( function (objResponse) {
                this.list = objResponse.body.list;
                this.labels = objResponse.body.labels;
                this.fields = objResponse.body.fields;
                this.reload = false;
            }.bind(this));
        },
        setOperatorCssClass: function (operator) {
            let objCssClass = {};
            objCssClass['operator'] = true;
            objCssClass[operator] = true;
            return objCssClass;
        },
        setFieldCssClass: function (field) {
            let objCssClass = {};
            objCssClass['field'] = true;
            objCssClass[field] = true;
            objCssClass['desc'] = this.order.hasOwnProperty(field) && this.order[field] === 'desc';
            objCssClass['asc'] = this.order.hasOwnProperty(field) && this.order[field] === 'asc';
            return objCssClass;
        },
        deleteItem: function(item) {
            if (!confirm(this.deleteConfirmLabel)) {
                return null;
            }
            this.reload = true;
            this.$http.post(item.operations.delete.href, {module: this.module,}, {emulateJSON: true, 'Content-Type': 'application/x-www-form-urlencoded'}
            ).then(function (objResponse) {
                if (objResponse.body.success && objResponse.ok) {
                    for (let i = 0; i < this.list.length; i++) {
                        if (this.list[i] === item) {
                            this.list.splice(i,1);
                        }
                    }
                }
                this.reload = false;
            }.bind(this));
        },
        callOperator: function ($e,operator,item) {
            switch (operator) {
                case 'delete':
                    $e.preventDefault();
                    this.deleteItem(item);
                    return false;
                default:
                    return null;
            }
        },
        sort: function (field, e) {
            this.order = {};
            let order = 'desc';
            if (e.target.classList.contains('desc')) {
                order = 'asc';
            }
            this.order[field] = order;
            this.fetch();
        }
    },
    mounted: function () {
        this.fetch();
    },
    props: {
        module: {
            type: String,
            default: null,
            required: true
        },
        addUrl: {
            type: String,
            default: null,
            required: false
        },
        addUrlLabel: {
            type: String,
            default: 'Neu',
            required: false
        },
        deleteConfirmLabel: {
            type: String,
            required: false,
            default: 'Sind Sie sicher, dass Sie Ihren Eintrag löschen wollen?'
        },
        operations: {
            type: Array,
            required: false,
            default: ['edit','delete']
        },
        addButtonPosition: {
            type: String,
            required: false,
            default: 'before'
        },
        order: {
            default: {},
            type: Object,
            required: false
        }
    },
    template:
        '<div class="table-list-component" style="position:relative;min-height:200px;" :id="\'list-id-\'+this.module">' +
            '<div class="table-list-component-container">' +
                '<div v-if="addUrl && addButtonPosition === \'before\'" class="operator add">' +
                    '<slot name="add" v-bind:addUrLabel="addUrlLabel" v-bind:addUrl="addUrl">' +
                        '<a class="btn primary" :href="addUrl" :title="addUrlLabel" v-html="addUrlLabel"></a>' +
                    '</slot>' +
                '</div>' +
                '<div v-if="list.length" class="table">' +
                    '<div class="thead">' +
                        '<div class="tr">' +
                            '<slot name="th" v-bind:fields="fields" v-bind:labels="labels">' +
                                '<div v-for="field in fields" class="th" v-bind:class="setFieldCssClass(field)" v-html="labels[field]" @click="sort(field, event)" style="cursor:pointer"></div>' +
                                '<div v-if="operations.length" class="th operations" v-html="labels[\'operations\']"></div>' +
                            '</slot>' +
                        '</div>' +
                    '</div>'+
                    '<div class="tbody">' +
                        '<div class="tr" v-for="item in list">' +
                            '<slot name="td" v-bind:fields="fields" v-bind:callOperator="callOperator" v-bind:operations="operations" v-bind:item="item">' +
                                '<div v-for="field in fields" class="td" v-bind:class="setFieldCssClass(field)" v-html="item[field]"></div>' +
                                '<div v-if="operations.length" class="td operations">' +
                                    '<div v-bind:class="setOperatorCssClass(operator)" v-for="operator in operations" v-if="item.operations[operator][\'href\']">' +
                                        '<a v-on:click="callOperator($event,operator,item)" :href="item.operations[operator][\'href\']" :title="item.operations[operator][\'label\']" v-html="item.operations[operator][\'label\']"></a>' +
                                    '</div>' +
                                '</div>' +
                            '</slot>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div v-if="addUrl && addButtonPosition === \'after\'" class="operator add">' +
                    '<slot name="add" v-bind:addUrLabel="addUrlLabel" v-bind:addUrl="addUrl">' +
                        '<a :href="addUrl" :title="addUrlLabel" v-html="addUrlLabel"></a>' +
                    '</slot>' +
                '</div>' +
            '</div>' +
            '<div class="reload" v-if="reload" style="position:absolute;top:0;width:100%;height:100%;display:flex;align-items:center;justify-content:center;"><img src="bundles/alnvcontaoformmanager/assets/loading.svg" alt=""></div>' +
        '</div>'
});