<template>
    <div>
        <div class="row table-panel">
            <div class="col-md-4 text-left">
            </div>
            <div class="col-md-4 text-left">
                <table class="table">
                    <tbody>
                        <tr>
                            <th>Код клиента</th>
                            <td>
                                {{ this.data.code }}
                            </td>
                        </tr>
                        <tr>
                            <th>ФИО</th>
                            <td>
                                {{ this.data.name }}
                            </td>
                        </tr>
                        <tr>
                            <th>E-mail</th>
                            <td>
                                {{ this.data.email }}
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <button class="button">
                                    сохранить
                                </button>
                            </th>
                            <th>
                                <button class="button">
                                    отменить
                                </button>
                            </th>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-md-4 text-left">
            </div>
        </div>
    </div>
</template>


<script>
    export default {
        name: 'profile-data',
        data() {
            return {
                data: [],
            }
        },
        created() {
            this.loadData();
        },
        methods: {
            loadData() {
                axios({
                    url: `/data/profile`,
                    method: 'GET'
                })
                    .then(response => {
                        this.data = response.data;
                    })
                    .catch(error => {

                    });
            }
        }
    }
</script>
