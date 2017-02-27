import app from 'flarum/app';
import addUsersListPane from 'issyrocks12/users-list/addUsersListPane';

app.initializers.add('avatar4eg-users-list', app => {
    addUsersListPane();
});
