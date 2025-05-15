from flask import Flask, render_template, request, redirect, url_for, session, jsonify
from flask_sqlalchemy import SQLAlchemy
from flask_login import LoginManager, UserMixin, login_user, logout_user, login_required, current_user
from azure.storage.blob import BlobServiceClient
import os
from datetime import timedelta

app = Flask(__name__)
app.secret_key = 'your_secret_key'
app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///site.db'
app.config['PERMANENT_SESSION_LIFETIME'] = timedelta(minutes=30)
db = SQLAlchemy(app)
login_manager = LoginManager(app)

# Azure Blob Storage
AZURE_CONNECTION_STRING = "Your_Connection_String"
AZURE_CONTAINER = "your-container-name"
blob_service = BlobServiceClient.from_connection_string(AZURE_CONNECTION_STRING)
container_client = blob_service.get_container_client(AZURE_CONTAINER)
class User(db.Model, UserMixin):
    id = db.Column(db.Integer, primary_key=True)
    username = db.Column(db.String(50), unique=True)
    password = db.Column(db.String(50))

class Image(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    filename = db.Column(db.String(100))
    user_id = db.Column(db.Integer)
    likes = db.Column(db.Integer, default=0)

class Comment(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    image_id = db.Column(db.Integer)
    user = db.Column(db.String(50))
    text = db.Column(db.String(200))

class Message(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    sender = db.Column(db.String(50))
    receiver = db.Column(db.String(50))
    content = db.Column(db.String(500))
@login_manager.user_loader
def load_user(user_id):
    return User.query.get(int(user_id))

@app.route('/')
def home():
    images = Image.query.all()
    return render_template('gallery.html', images=images)

@app.route('/signup', methods=['GET', 'POST'])
def signup():
    if request.method == 'POST':
        user = User(username=request.form['username'], password=request.form['password'])
        db.session.add(user)
        db.session.commit()
        return redirect('/login')
    return render_template('signup.html')

@app.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        user = User.query.filter_by(username=request.form['username'], password=request.form['password']).first()
        if user:
            login_user(user)
            return redirect('/')
    return render_template('login.html')

@app.route('/logout')
def logout():
    logout_user()
    return redirect('/')

@app.route('/upload', methods=['POST'])
@login_required
def upload():
    f = request.files['image']
    blob_client = container_client.get_blob_client(f.filename)
    blob_client.upload_blob(f, overwrite=True)

    new_img = Image(filename=f.filename, user_id=current_user.id)
    db.session.add(new_img)
    db.session.commit()
    return redirect('/')

@app.route('/like/<int:image_id>')
@login_required
def like(image_id):
    img = Image.query.get(image_id)
    img.likes += 1
    db.session.commit()
    return redirect('/')

@app.route('/comment/<int:image_id>', methods=['POST'])
@login_required
def comment(image_id):
    text = request.form['comment']
    new_comment = Comment(image_id=image_id, user=current_user.username, text=text)
    db.session.add(new_comment)
    db.session.commit()
    return redirect('/')

@app.route('/search')
def search():
    query = request.args.get('q', '')
    images = Image.query.filter(Image.filename.contains(query)).all()
    return render_template('gallery.html', images=images)

@app.route('/messages', methods=['GET', 'POST'])
@login_required
def messages():
    if request.method == 'POST':
        msg = Message(sender=current_user.username, receiver=request.form['to'], content=request.form['content'])
        db.session.add(msg)
        db.session.commit()
    messages = Message.query.filter_by(receiver=current_user.username).all()
    return render_template('messages.html', messages=messages)
@app.context_processor
def inject_blob_url():
    def get_blob_url(filename):
        return f"https://{blob_service.account_name}.blob.core.windows.net/{AZURE_CONTAINER}/{filename}"
    return dict(get_blob_url=get_blob_url)

if __name__ == '__main__':
    with app.app_context():
        db.create_all()
    app.run(debug=True)
