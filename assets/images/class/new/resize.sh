#!/bin/bash

# Function to display usage
usage() {
    echo "Usage: $0 -i <input-file> -o <output-file> -percent <percentage>"
    echo "  -i <input-file>   : Input image file"
    echo "  -o <output-file>  : Output image file (if not set, overwrite input file)"
    echo "  -percent <percentage> : Percentage to reduce the image size"
    exit 1
}

# Parse command-line arguments
while [[ "$#" -gt 0 ]]; do
    case $1 in
        -i) input_file="$2"; shift ;;
        -o) output_file="$2"; shift ;;
        -percent) percentage="$2"; shift ;;
        *) usage ;;
    esac
    shift
done

# Check if input file and percentage are set
if [ -z "$input_file" ] || [ -z "$percentage" ]; then
    usage
fi

# If output file is not set, overwrite the input file
if [ -z "$output_file" ]; then
    output_file="$input_file"
fi

# Resize the image using ImageMagick's convert command
convert "$input_file" -resize "$percentage%" "$output_file"

# Check if the command was successful
if [ $? -eq 0 ]; then
    echo "Image resized successfully."
else
    echo "Failed to resize the image."
    exit 1
fi
