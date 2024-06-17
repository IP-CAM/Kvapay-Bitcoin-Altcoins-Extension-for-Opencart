#!/bin/sh
#  Run script and pass version: ./op-release.sh

set -e

#enviroment variables
tmp_dir=tmp
release_name=kvapay-opencart.ocmod.zip
release_dir_name=kvapay-opencart

echo "Starting process..."

if [ -d  "${tmp_dir}" ]; then
  rm -rf "${tmp_dir}"
fi

rm -rf  kvapay-opencart2.ocmod.zip
rm -rf  kvapay-opencart2_3.ocmod.zip
rm -rf  kvapay-opencart3.ocmod.zip

mkdir $tmp_dir
rsync -a ./opencart2-plugin ${tmp_dir}
rsync -a ./opencart2.3-plugin ${tmp_dir}
rsync -a ./opencart3-plugin ${tmp_dir}


echo "Compressing release folder..."


cd $tmp_dir/opencart2-plugin && zip -r "kvapay-opencart2.ocmod.zip" upload && cd ..
cd opencart2.3-plugin && zip -r "kvapay-opencart2_3.ocmod.zip" upload && cd ..
cd opencart3-plugin && zip -r "kvapay-opencart3.ocmod.zip" upload && cd ../..

mv "$tmp_dir/opencart2-plugin/kvapay-opencart2.ocmod.zip" .
mv "$tmp_dir/opencart2.3-plugin/kvapay-opencart2_3.ocmod.zip" .
mv "$tmp_dir/opencart3-plugin/kvapay-opencart3.ocmod.zip" .
rm -rf $tmp_dir

echo ""
echo "Release folder is completed."
echo ""