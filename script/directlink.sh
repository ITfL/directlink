#!/bin/bash

# run script after installing directlink plugin in moodle

#installing necessary crypt package
echo "==== Begin script ===="
echo ""
echo "installing necessary crypt package (libmcrypt4), cifs-utils and smbfs"
sudo apt-get install libmcrypt4 cifs-utils smbfs php5-mcrypt
sudo php5enmod mcrypt
echo ""


check_input(){
path=$1
echo "Is this path correct? "
read -p "(Y / N):" VAR_INPUT
# Sanitize input and assign to new variable
export VAR_CLEAN_1=$VAR_INPUT
#echo "${VAR_CLEAN_1}"

	sudo mkdir -p $path
	echo "folder was created or was already"
	input1="$VAR_CLEAN_1"
	#echo $input1
	if [[ "$input1" == "N" ]];
		then wwwdata_path
	elif [[ "$input1" == "Y" ]];
		then 
			sudo chown www-data $path
	else
		echo "Please type Y or N"; 
		check_input "$path"
	fi
}


wwwdata_path(){
	echo "Please enter path for mounting shares (folder will be created if it doesn't exist):"
	read answer
	check_input "$answer"
}

add_wwwdata(){
	echo "Add www-data to sudoers file"
	add=$(sudo cat /etc/sudoers | grep www-data)
	if [[ "$add" == "" ]];
		then 
		echo ""
		echo "Adding www-data to sudoers list!"
		echo ""
		echo 'www-data ALL=(ALL) NOPASSWD:ALL' | sudo tee -a /etc/sudoers
		echo ""
		echo "www-data added to sudoers list"
		echo ""
	else
		echo ""
		echo ""
		echo "!!!"
		echo "Please add user www-data manually to sudoers list, www-data already included."
		echo "!!!"
		echo ""
		echo ""
	fi
	 echo "==== End script ===="
	
	#echo 'www-data ALL=(ALL) NOPASSWD:ALL' >> /etc/sudoers
	}

wwwdata_path
add_wwwdata
