<template>
  <div>
    <b-row>
      <b-col>
        <b-form-input v-model="title" placeholder="Give me a title"></b-form-input>
      </b-col>
    </b-row>
    
    <b-row>
      <b-col>
        <b-form-input v-model="subtitle" placeholder="Give me a sub-title"></b-form-input>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <tiptap-vuetify 
        v-model="content" 
        :extensions="extensions" 
        :toolbar-attributes="{ color: 'blue', dark: true }"
        placeholder="Write Something..."/>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <div class="mt-3">
          <b-button-group class="pull-right">
            <b-button variant="success" @click="publishArticle">
              <b-spinner
                v-show="publishing"
                variant="light"
                small
              ></b-spinner> Publish
            </b-button>
            <b-button variant="warning" @click="saveArticle">
              <b-spinner
                v-show="drafting"
                variant="light"
                small
              ></b-spinner> Draft
            </b-button>
            <b-button variant="danger" @click="cancelArticle">Clear</b-button>
            <b-button variant="info" href="/articles">View All Articles</b-button>
          </b-button-group>
        </div>
      </b-col>
    </b-row>
  </div>
</template>

<script>
import {
  // component
  TiptapVuetify,
  // extensions
  Heading,
  Bold,
  Italic,
  Strike,
  Underline,
  Code,
  Paragraph,
  BulletList,
  OrderedList,
  ListItem,
  Link,
  Blockquote,
  HardBreak,
  HorizontalRule,
  History
} from "tiptap-vuetify";

export default {
  props: {
    article: Object
  },
  components: { TiptapVuetify },
  data: () => ({
    extensions: [
      History,
      Blockquote,
      Link,
      Underline,
      Bold,
      Strike,
      Italic,
      ListItem, // if you need to use a list (BulletList, OrderedList)
      BulletList,
      OrderedList,
      [
        Heading,
        {
          // Options that fall into the tiptap's extension
          options: {
            levels: [1, 2, 3]
          }
        }
      ]
    ],
    title: null,
    subtitle: null,
    content: null,
    publishing: false,
    drafting: false
  }),
  methods: {
    saveArticle: function() {
      let params = {
        'title': this.title,
        'subtitle': this.subtitle,
        'content': this.content
      }

      this.drafting = true

      if(this.article) {
        axios.put('/articles/' + this.article.id, params)
        .then(response => {
          Swal.fire(
            'Good job!',
            'Your article has been saved.',
            'success'
          )
        })
        .catch(error => {
          Swal.fire(
            'Something went wrong!',
            'Please try again.',
            'error'
          )
        })
        .finally(final => {
          this.drafting = false
        })

        return
      }
      
      axios.post('/articles', params)
      .then(response => {
        Swal.fire(
          'Good job!',
          'Your article has been saved.',
          'success'
        )
      })
      .catch(error => {
        Swal.fire(
          'Something went wrong!',
          'Please try again.',
          'error'
        )
      })
      .finally(final => {
        this.drafting = false
      })
    },
    publishArticle: function() {
      let params = {
        'title': this.title,
        'subtitle': this.subtitle,
        'content': this.content,
        'publish': true
      }
      
      this.publishing = true

      if(this.article) {
        axios.put('/articles/' + this.article.id, params)
        .then(response => {
          Swal.fire(
            'Good job!',
            'Your article has been published.',
            'success'
          )
        })
        .catch(error => {
          Swal.fire(
            'Something went wrong!',
            'Please try again.',
            'error'
          )
        })
        .finally(final => {
          this.publishing = false
        })

        return
      }
      
      axios.post('/articles', params)
      .then(response => {
        Swal.fire(
          'Good job!',
          'Your article has been published.',
          'success'
        )
      })
      .catch(error => {
        Swal.fire(
          'Something went wrong!',
          'Please try again.',
          'error'
        )
      })
      .finally(final => {
        this.publishing = false
      })
    },
    cancelArticle: function() {
      this.title = null
      this.subtitle = null
      this.content = null
    }
  },
  mounted() {
    
    if(this.article) {
      this.title = this.article.title
      this.subtitle = this.article.subtitle
      this.content = this.article.content
    }
    
  }
};
</script>
