<template>
<div class="card"><div class="card-body">
<p v-if="impevals.length > 0">
    <a class="btn btn-link emoji" v-for="impeval in impevals" @click="deleteScore(impeval.id, impeval.score)">
        <span class="profimg prfmini" v-bind:style="`background-image:url(/users/icon/${impeval.user_id})`"></span> {{emojis[impeval.score]}}
    </a>
</p>
<p v-else>評価はありません。</p>

<div style="border-top:solid #ccc 1px; text-align:center">
    <a class="btn btn-link emoji" @click="putScore(imagepost_id, 5)">
        <span>{{emojis[5]}}</span>
    </a>
    <a class="btn btn-link emoji" @click="putScore(imagepost_id, 4)">
        <span>{{emojis[4]}}</span>
    </a>
    <a class="btn btn-link emoji" @click="putScore(imagepost_id, 3)">
        <span>{{emojis[3]}}</span>
    </a>
    <a class="btn btn-link emoji" @click="putScore(imagepost_id, 2)">
        <span>{{emojis[2]}}</span>
    </a>
    <a class="btn btn-link emoji" @click="putScore(imagepost_id, 1)">
        <span>{{emojis[1]}}</span>
    </a>
</div>
</div></div>
</template>
<script>
export default {
    data() {
        return {
            impevals: [],
            myevals: {},
        };
    },

    props: ['import_evals', 'myuser_id', 'imagepost_id', 'emojis'],

    mounted() {
        for (var i = 0; i < this.import_evals.length; i++) {
            this.impevals.push(this.import_evals[i]);
            if (this.myuser_id == this.import_evals[i].user_id) {
                this.myevals[this.import_evals[i].score] = 1;
            }
        }
    },

    methods: {
        putScore(id, score) {
            if (typeof(this.myevals[score]) != 'undefined') {
                return;
            }
            $('#loading').removeClass('hidden');

            let self = this;
            let url = '/api/imagepost/eval/add';
            axios.post(url, {imagepost_id:id, score:score})
            .then(function(response) {
                if (typeof(response.data.data) != 'undefined') {
                    self.impevals.push(response.data.data);
                    self.myevals[response.data.data.score] = 1;
                }
                $('#loading').addClass('hidden');
            })
            .catch(error => {
                $('#loading').addClass('hidden');
            });
        },

        deleteScore(eval_id, score) {
            if (typeof(this.myevals[score]) == 'undefined') {
                return;
            }
            $('#loading').removeClass('hidden');

            let self = this;
            let url = '/api/imagepost/eval/delete/' + eval_id;
            axios.delete(url)
            .then(function(response) {
                if (typeof(response.data.data) != 'undefined') {
                    delete self.myevals[response.data.data.score];
                    for (var i = 0; i < self.impevals.length; i++) {
                        if (self.impevals[i].id == response.data.data.id) {
                            self.impevals.splice(i, 1);
                            break;
                        }
                    }
                    $('#loading').addClass('hidden');
                }
            })
            .catch(error => {
                $('#loading').addClass('hidden');
            });
        }
    }
}
</script>
<style scoped>
.emoji {
    font-size:1.5rem;
}
</style>
