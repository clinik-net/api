var usersCursor = db.users.find();
var usersArray = usersCursor.toArray();

for (i = 0; i < usersArray.length; i++) {
    var item = usersArray[i];
    //print(JSON.stringify(item));
    db.users.update({
        _id: item._id
    }, {
        $set: {
            email: item._id
        }
    });
    var newItem = db.users.findOne({email: item._id});
    print(JSON.stringify(newItem));
}