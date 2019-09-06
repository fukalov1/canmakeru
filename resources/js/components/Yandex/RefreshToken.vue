<template>
    <div>
        Обновление токена
        <span class="h3 danger" v-if="!clientId">
            Заполните данные для доступа к Яндекс.Диску для приложений в Конфигурации сайта
        </span>
        <button v-if="!processing" type="button">запустить</button>
        <span class="h4" v-else>
            {{ message }}
        </span>
    </div>
</template>

<script>
    export default {
        name: 'yandex-refresh-token',
        props: {
            clientId: {
                type: String,
                default: null
            },
            pass: {
                type: String,
                default: null
            },
            token: {
                type: String,
                default: null
            }
        },
        data() {
            return {
                processing: false,
                message: 'Идет обновления токена.'
            }
        },
        methods: {

        }
    }
</script>
