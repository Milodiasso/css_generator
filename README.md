# NAME
  
  css_generator - sprite generator for HTML use CSS_GENERATOR(1)
  
  SYNOPSIS:
  
  php css_generator [OPTIONS]. . . assets_folder
  
  Concatenate all images inside a folder in one sprite and write a style sheet ready to use.
  Mandatory arguments to long options are mandatory for short options too.
  
# OPTIONS

  -r, -- recursive
  Look for images into the assets_folder passed as arguement and all of its subdirectories.

  -i, -- output-image=IMAGE
  Name of the generated image. If blank, the default name is « sprite.png ».

  -s, -- output-style=STYLE
  Name of the generated stylesheet. If blank, the default name is « style.css ».

  BONUS OPTIONS

  -p, -- padding=NUMBER
  Add padding between images of NUMBER pixels.

  -o, -- override-size=SIZE
  Force each images of the sprite to fit a size of SIZExSIZE pixels.

  -c, -- columns_number=NUMBER
  The maximum number of elements to be generated horizontally.
