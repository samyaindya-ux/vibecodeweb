from PIL import Image

img = Image.open('i:/xampp/htdocs/vibecodeweb/images/new_site_logo.png')
img = img.convert("RGBA")
pixels = img.load()

for y in range(img.height):
    for x in range(img.width):
        r, g, b, a = pixels[x, y]
        m = max(r, g, b)
        if m == 0:
            pixels[x, y] = (0, 0, 0, 0)
        else:
            factor = 255.0 / m
            new_r = min(255, int(r * factor))
            new_g = min(255, int(g * factor))
            new_b = min(255, int(b * factor))
            pixels[x, y] = (new_r, new_g, new_b, m)

img.save('i:/xampp/htdocs/vibecodeweb/images/new_site_logo_transparent.png', 'PNG')
print("Saved transparent image.")
