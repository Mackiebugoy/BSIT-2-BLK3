from tkinter import *
import mysql.connector
from tkinter import messagebox

def db_connection():
    try:
        mydb = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="mae_db"
        )
        return mydb
    except mysql.connector.Error as err:
        messagebox.showerror("Database Error", f"{err}")
        return None

def login():
    username = entry_email.get()
    password = entry_pass.get()
    mydb = db_connection()
    if not mydb:
        return
    mycursor = mydb.cursor()
    sql = "SELECT role FROM users WHERE username = %s AND password = %s"
    mycursor.execute(sql, (username, password))
    result = mycursor.fetchone()
    if result:
        role = result[0]
        if role == "admin":
            print("Admin Access Granted")
            admin_welcome(username)
        elif role == "staff":
            print("Staff Access Granted")
            staff_welcome(username)
    else:
        messagebox.showerror("Login Failed", "Invalid Username or Password")

def admin_welcome(username):
    welcome_win = Toplevel()
    welcome_win.geometry("300x200")
    welcome_win.title("Admin")
    welcome_win.config(padx=20, pady=20)

    Label(welcome_win, text=f"Welcome, {username}!", font=('Arial', 16)).pack(pady=10)
    mydb = db_connection()
    if mydb:
        messagebox.showinfo("Database", "Connected to Database")
        mydb.close()
    else:
        messagebox.showerror("Database", "Connection failed!")

def staff_welcome(username):
    welcome_win = Toplevel()
    welcome_win.geometry("300x200")
    welcome_win.title("Welcome, Staff!")
    welcome_win.config(padx=20, pady=20)

    Label(welcome_win, text=f"Welcome, {username}!", font=('Arial', 16)).pack(pady=10)
    mydb = db_connection()
    if mydb:
        messagebox.showinfo("Database", "Connected to Database")
        mydb.close()
    else:
        messagebox.showerror("Database", "Connection failed!")

# Use a simpler approach for rounded buttons with ttk
def create_rounded_button(parent, text, command, width=15, height=2, bg="#4CAF50", fg="white", font=('Arial', 12, 'bold')):
    # Create a regular button with styling
    style_name = f"RoundedButton.TButton"
    
    # Create a frame to hold the button with the same bg as parent
    frame = Frame(parent, bg=parent["bg"])
    
    # Create a regular button that will reliably handle clicks
    button = Button(frame, text=text, command=command, 
                   bg=bg, fg=fg, font=font,
                   width=width, height=height,
                   relief=RAISED, borderwidth=2,
                   highlightbackground="#C71585", highlightthickness=2,
                   activebackground="#C71585",  # Darker shade for hover
                   cursor="hand2")  # Hand cursor on hover
    button.pack(padx=5, pady=5)
    
    return frame

# Create custom style for entry fields
def create_custom_entry(parent, width=30, show=None):
    entry_frame = Frame(parent, bg="#FF69B4", bd=2, relief=RAISED, padx=5, pady=5, highlightbackground="#C71585", highlightthickness=2)
    entry = Entry(entry_frame, width=width, font=('Arial', 14), bd=0, show=show, bg="#FFF0F5")
    entry.pack(fill=BOTH, expand=True, ipady=8)
    return entry_frame, entry

# Main window setup
root = Tk()
root.geometry("400x400")
root.title("Tressia_First_Gui")
root.config(padx=30, pady=30, bg="#FFD6E0")  # Pink background

# Title at the top
Label(root, text='LOGIN', font=('Arial', 24, 'bold'), bg="#FFD6E0", fg="#FF1493").pack(pady=(0, 20))

# Username field with label
Label(root, text='Username:', font=('Arial', 16, 'bold'), bg="#FFD6E0", fg="#8B008B").pack(anchor='w')
username_frame, entry_email = create_custom_entry(root)
username_frame.pack(fill=X, pady=(5, 15))

# Password field with label
Label(root, text='Password:', font=('Arial', 16, 'bold'), bg="#FFD6E0", fg="#8B008B").pack(anchor='w')
password_frame, entry_pass = create_custom_entry(root, show='*')
password_frame.pack(fill=X, pady=(5, 25))

# Login button
login_btn = create_rounded_button(root, "LOGIN", login, width=20, height=2, bg="#FF1493", fg="white", font=('Arial', 16, 'bold'))
login_btn.pack(pady=20)

root.mainloop()