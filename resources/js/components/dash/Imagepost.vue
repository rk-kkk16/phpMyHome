<template>
<section id="dash-imagepost" class="card">
    <div class="card-header"><a href="/imagepost">写真共有</a></div>
    <div class="card-body">
        <div id="dash-imp-loading" style="text-align:center;width:60%;"><img src="/css/loading.gif"></div>

        <div class="row" :class="{'hidden':!load_end}">
        <template v-if="rows.length > 0">
            <div class="col-md-4 col-sm-12" v-for="post in rows">
              <div class="card">
                <div class="card-header">
                    <span class="profimg prfmini" v-bind:style="`background-image:url(/users/icon/${post.user_id})`"></span> {{post.user.name}}
                </div>
                <div class="card-body imp-photo">
                    <a v-bind:href="`/imagepost/${post.id}`">
                    <img v-bind:src="`/imagepost/photo/${post.id}?img_size=300&base_w=300`">
                    </a>

                    <br><br>
                    <p style="text-align:right"><i class="fas fa-calendar-alt"></i> {{post.created_at|moment}}</p>
                </div>
              </div>
            </div>
        </template>
        <p v-else>写真はありません。</p>
        </div>
        <p style="text-align:right"><a href="/imagepost">&gt;&gt;写真共有へ</a></p>
    </div>
</section>
</template>
<script>
import moment from 'moment';
export default {
    filters: {
        moment: function (date) {
            return moment(date).format('YYYY/MM/DD HH:mm');
        }
    },

    data() {
        return {
            rows: [],
            load_end: false,
        };
    },

    props: [],

    mounted() {
        let self = this;
        let url = '/api/imagepost/list?num=6';
        axios.get(url).then(function(response) {
            $('#dash-imp-loading').hide();
            self.load_end = true;
            for (var i = 0; i < response.data.data.length; i++) {
                self.rows.push(response.data.data[i]);
            }
        })
        .catch(error => {
            $('#dash-imp-loading').hide();
            self.load_end = true;
        });
    },
}
</script>
<style scoped>
.col-md-4, .col-sm-12 {
    margin-bottom:1em;
}

.imp-photo img {
    border:solid 1px #ccc;
    width:98%;
}
</style>
