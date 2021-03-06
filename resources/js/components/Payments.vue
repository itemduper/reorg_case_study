<template>
    <div>
        <div class="pb-2 mx-auto d-flex" style="max-width: 800px">
            <recipient-search v-on:selected="recipientSelected" v-on:reset="resetTable" style="padding-left: 40px" />
            <div class="pl-2">
                <a :href="exportLink" target="_blank" class="export-button" :class="{'invisible': !showExportLink}">
                    <font-awesome-icon :icon="['fas', 'download']" style="position: relative; top: 12;" />
                </a>
            </div>
        </div>

        <payments-table :cols="columns" :rows="rows" />
    </div>
</template>

<script>
    export default {
        data() {
            return {
                /**
                 * Columns to display in the Payments Table
                 */
                columns: [],
                /**
                 * Rows to display in the Payments Table
                 */
                rows: [],
                /**
                 * Recipient selected in the Search
                 */
                selectedRecipient: null,
            }
        },
        methods: {
            /**
             * Gets called when a recipient is selected in the Recipient Search
             * 
             * @param {Object} recipient Recipient selected in the search.
             */
            recipientSelected(recipient) {
                this.resetTable();

                this.selectedRecipient = recipient;

                this.$http.get('/api/payment/recipient/'+recipient.type+'/'+recipient.id).then(response => {
                    this.fillTable(response.data.data);
                });
            },

            /**
             * Fills the table with the data provided and specifies columns based on the keys.
             * 
             * @param {Array} data Array of objects to fill the table with.
             */
            fillTable(data) {
                let column_show = [];
                let rows = [];
                data.forEach(row => {
                    Object.keys(row).forEach(key => {
                        if(row[key] != null) column_show[key] = true;
                    });
                    rows.push(row);
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
                this.rows = rows;
            },

            /**
             * Resets the table to a blank state and removes the selectedRecipient
             */
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

                let rows = [];
                for(let i = 0; i < 5; i++) {
                    rows.push({ default1: " ", default2: " ", default3: " ", default4: " ", default5: " ", default6: " " });
                }
                this.rows = rows;
            }
        },
        computed: {
            /**
             * Returns true if the Export Link should be shown.
             */
            showExportLink() {
                return (this.selectedRecipient != null);
            },
            /**
             * Returns the Export Link used in the Export Button
             */
            exportLink() {
                if(this.showExportLink) {
                    return '/payments/export/'+this.selectedRecipient.type+'/'+this.selectedRecipient.id;
                } else {
                    return '/';
                }
            }
        },
        mounted() {
            this.resetTable();
        }
    }
</script>

<style>
    .export-button {
        font-size: 2em;
    }
</style>