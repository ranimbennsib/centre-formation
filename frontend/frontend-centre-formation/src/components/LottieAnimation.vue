<!-- src/components/LottieAnimation.vue -->
<template>
  <div ref="lottieContainer" :style="containerStyle"></div>
</template>

<script>
import lottie from 'lottie-web'

export default {
  name: 'LottieAnimation',
  props: {
    animationData: {
      type: Object,
      required: true
    },
    width: {
      type: [String, Number],
      default: '100%'
    },
    height: {
      type: [String, Number],
      default: '100%'
    },
    loop: {
      type: Boolean,
      default: true
    },
    autoplay: {
      type: Boolean,
      default: true
    }
  },
  computed: {
    containerStyle() {
      return {
        width: typeof this.width === 'number' ? `${this.width}px` : this.width,
        height: typeof this.height === 'number' ? `${this.height}px` : this.height,
        display: 'inline-block'
      }
    }
  },
  mounted() {
    this.loadAnimation()
  },
  beforeUnmount() {
    if (this.anim) {
      this.anim.destroy()
    }
  },
  methods: {
    loadAnimation() {
      this.anim = lottie.loadAnimation({
        container: this.$refs.lottieContainer,
        renderer: 'svg',
        loop: this.loop,
        autoplay: this.autoplay,
        animationData: this.animationData
      })
    },
    play() {
      if (this.anim) this.anim.play()
    },
    pause() {
      if (this.anim) this.anim.pause()
    },
    stop() {
      if (this.anim) this.anim.stop()
    }
  }
}
</script>