from tkinter import *
import mysql.connector
from tkinter import messagebox

def db_connection():
    try:
        mydb = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="mac_db"
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
                   relief=FLAT, borderwidth=0,
                   activebackground="#45a049",  # Darker shade for hover
                   cursor="hand2")  # Hand cursor on hover
    button.pack(padx=5, pady=5)
    
    return frame

# Create custom style for entry fields
def create_custom_entry(parent, width=30, show=None):
    entry_frame = Frame(parent, bg="#E0E0E0", bd=2, relief=FLAT, padx=5, pady=5)
    entry = Entry(entry_frame, width=width, font=('Arial', 12), bd=0, show=show)
    entry.pack(fill=BOTH, expand=True, ipady=5)
    return entry_frame, entry

# Main window setup
root = Tk()
root.geometry("400x400")
root.title("Secure Login")
root.config(padx=30, pady=30, bg="#F0F0F0")

# Title at the top
Label(root, text='LOGIN', font=('Arial', 20, 'bold'), bg="#F0F0F0", fg="#333333").pack(pady=(0, 20))

# Username field with label
Label(root, text='Username:', font=('Arial', 14), bg="#F0F0F0", fg="#555555").pack(anchor='w')
username_frame, entry_email = create_custom_entry(root)
username_frame.pack(fill=X, pady=(5, 15))

# Password field with label
Label(root, text='Password:', font=('Arial', 14), bg="#F0F0F0", fg="#555555").pack(anchor='w')
password_frame, entry_pass = create_custom_entry(root, show='*')
password_frame.pack(fill=X, pady=(5, 25))

# Login button
login_btn = create_rounded_button(root, "LOGIN", login, width=15, height=2)
login_btn.pack(pady=10)

root.mainloop()