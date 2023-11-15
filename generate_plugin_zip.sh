
# Create a directory to hold the plugin files
PLUGIN_DIR="codewp-helper"
# Remove the previous zip file
rm -f codewp-helper.zip

mkdir $PLUGIN_DIR

# Copy the necessary files to the plugin directory
cp -r includes $PLUGIN_DIR/
cp -r build $PLUGIN_DIR/
cp readme.txt $PLUGIN_DIR/
cp codewp-helper.php $PLUGIN_DIR/



# Zip the plugin directory
zip -r codewp-helper.zip $PLUGIN_DIR/

# Remove the plugin directory
rm -rf $PLUGIN_DIR
