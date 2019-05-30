<template>
    <div>
        <p>{{title}}</p>
        <p>{{data}}</p>
    </div>
</template>

<script>
export default {
    name: 'TourGuideList',
    props: {
        title: String,
        data: Array
    }
}
</script>
