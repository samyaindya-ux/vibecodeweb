from PIL import Image
import os

img_path = 'i:/xampp/htdocs/vibecodeweb/images/5logo.jpg'
out_dir = 'i:/xampp/htdocs/vibecodeweb/images/'

img = Image.open(img_path)
width, height = img.size

cols, rows = 3, 2
w, h = width // cols, height // rows

count = 0
for r in range(rows):
    for c in range(cols):
        left = c * w
        upper = r * h
        right = left + w
        lower = upper + h
        cropped = img.crop((left, upper, right, lower))
        cropped.save(os.path.join(out_dir, f'logo_{count}.jpg'))
        count += 1

print('Successfully cropped 6 images.')
