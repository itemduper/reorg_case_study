<template>
    <div>
        <div class="pb-2 mx-auto d-flex" style="max-width: 800px">
            <recipient-search v-on:selected="recipientSelected" v-on:reset="resetTable" />
            <div class="pl-2">
                <a :href="exportLink" target="_blank" class="export-button" :class="{'invisible': !showExportLink}">
                    <font-awesome-icon :icon="['fas', 'download']" style="position: relative; top: 12;" />
                </a>
            </div>
        </div>

        <payments-table :cols="columns" :rows="tableData" />
    </div>
</template>

<script>
    export default {
        data() {
            return {
                columns: [
                    { field: "default1", key: "a", title: " " },
                    { field: "default2", key: "b", title: " " },
                    { field: "default3", key: "c", title: " " },
                    { field: "default4", key: "d", title: " " },
                    { field: "default5", key: "e", title: " " },
                    { field: "default6", key: "f", title: " " },
                ],
                tableData: [
                    { default1: " ", default2: " ", default3: " ", default4: " ", default5: " ", default6: " " },
                    { default1: " ", default2: " ", default3: " ", default4: " ", default5: " ", default6: " " },
                    { default1: " ", default2: " ", default3: " ", default4: " ", default5: " ", default6: " " },
                    { default1: " ", default2: " ", default3: " ", default4: " ", default5: " ", default6: " " },
                    { default1: " ", default2: " ", default3: " ", default4: " ", default5: " ", default6: " " },
                ],
                selectedRecipient: null,
            }
        },
        methods: {
            recipientSelected(recipient) {
                this.resetTable();

                this.selectedRecipient = recipient;

                this.$http.get('/api/payment/recipient/'+recipient.type+'/'+recipient.id).then(response => {
                    this.fillTable(response.data.data);
                });
            },

            fillTable(data) {
                let column_show = [];
                let tableData = [];
                data.forEach(row => {
                    Object.keys(row).forEach(key => {
                        if(row[key] != null) column_show[key] = true;
                    });
                    tableData.push(row);
                });
                
                let columns = [];
                let i = 0;
                Object.keys(data[0]).forEach(column_name => {
                    if(column_show[column_name] == true) {
                        let column = { field: column_name, key: 'column_'+i, title: column_name, sortBy: "" }
                        if(i === 0) column['sortBy'] = 'asc';
                        i++;

                        columns.push(column);
                    }
                });

                this.columns = columns;
                this.tableData = tableData;
            },

            resetTable() {
                this.selectedRecipient = null;

                this.columns = [
                    { field: "default1", key: "a", title: " " },
                    { field: "default2", key: "b", title: " " },
                    { field: "default3", key: "c", title: " " },
                    { field: "default4", key: "d", title: " " },
                    { field: "default5", key: "e", title: " " },
                    { field: "default6", key: "f", title: " " },
                ];

                let tableData = [];
                for(let i = 0; i < 5; i++) {
                    tableData.push({ default1: " ", default2: " ", default3: " ", default4: " ", default5: " ", default6: " " });
                }
                this.tableData = tableData;
            }
        },
        computed: {
            showExportLink() {
                return (this.selectedRecipient != null);
            },
            exportLink() {
                if(this.showExportLink) {
                    return '/payments/export/'+this.selectedRecipient.type+'/'+this.selectedRecipient.id;
                } else {
                    return '/';
                }
            }
        },
        mounted() {
        }
    }
</script>

<style>
    .export-button {
        font-size: 2em;
    }
</style>