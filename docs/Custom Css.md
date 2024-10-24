## Custom Css

#### Glassmorphism

> note: this is preconfigured to bootstrap 5

```css
/* glass-default */
.glass-default {
  /* Glassmorphism default to bg-body */
  backdrop-filter: blur(25px) saturate(180%);
  -webkit-backdrop-filter: blur(25px) saturate(180%);
  background-color: rgba(var(--bs-body-bg-rgb), var(--bs-bg-opacity));
}
```

to use this css, you can add the class `glass-default` to your element

```html
<div class="glass-default">
  <!-- your content here -->
</div>
```

if you want to change the backgtound opacity, you can add the `--bs-bg-opacity` variable to the element

```html
<div class="glass-default bg-opacity-50">
  <!-- your content here -->
</div>
```

other classes that you can use are:

```html
<!-- default / can adjust background opacity -->
<div class="glass-default"></div>

<!-- 10% background opacity -->
<div class="glass-10"></div>
<!-- 5% background opacity -->
<div class="glass-5"></div>
<!-- 3% background opacity -->
<div class="glass-3"></div>
```

#### Blur Background

```css
/* blur background */
.bg-blur-10 {
  backdrop-filter: blur(10px);
}
```

to use this css, you can add the class `bg-blur-10` to your element

```html
<div class="bg-blur-10">
  <!-- your content here -->
</div>
```

other classes that you can use are:

```html
<!-- 5px blur -->
<div class="bg-blur-5"></div>
<!-- 3px blur -->
<div class="bg-blur-3"></div>
```

