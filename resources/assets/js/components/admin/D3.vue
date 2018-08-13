<template>
  <div class="container">
    <tree :data="tree" :node-text="name" layoutType="euclidien">
    </tree>
  </div>
</template>

<script>
import {tree} from 'vued3tree'

export default {
  data () {
    return {
      tree: {
        name: 'index',
        children: [
          {
            name: 'post1',
            children:
            [
              {name: 'subject1'},
              {name: 'subject2'}
            ]
          },
          {
            name: 'post2',
            children: [
              {name: 'subject3'},
              {name: 'subject4'}
            ]
          }
        ]
      }
    }
  },
  components: { tree }
}
</script>